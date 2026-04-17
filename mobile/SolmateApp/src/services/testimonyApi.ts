import {apiDelete, apiGet, apiPostForm} from './api';

export type TestimonyStatus = 'pending' | 'approved' | 'rejected' | string;

export type TestimonyUserSummary = {
  id: number;
  name?: string | null;
};

export type TestimonyServiceRequestSummary = {
  id: number;
  user_id?: number;
  technician_id?: number | null;
  request_type?: string | null;
  status?: string | null;
  date_needed?: string | null;
};

export type TestimonyInspectionRequestSummary = {
  id: number;
  user_id?: number;
  technician_id?: number | null;
  status?: string | null;
  date_needed?: string | null;
};

export type TestimonyImage = {
  id: number;
  testimony_id: number;
  image_path: string;
  image_url?: string | null;
  created_at?: string;
  updated_at?: string;
};

export type Testimony = {
  id: number;
  user_id: number;
  service_request_id?: number | null;
  inspection_request_id?: number | null;
  rating: number;
  title?: string | null;
  message: string;
  status: TestimonyStatus;
  admin_note?: string | null;
  created_at?: string;
  updated_at?: string;
  user?: TestimonyUserSummary | null;
  serviceRequest?: TestimonyServiceRequestSummary | null;
  inspectionRequest?: TestimonyInspectionRequestSummary | null;
  images?: TestimonyImage[];
};

type RawTestimonyImage = {
  id: number;
  testimony_id: number;
  image_path: string;
  image_url?: string | null;
  created_at?: string;
  updated_at?: string;
};

type RawTestimony = Omit<Testimony, 'serviceRequest' | 'inspectionRequest' | 'images'> & {
  service_request?: TestimonyServiceRequestSummary | null;
  inspection_request?: TestimonyInspectionRequestSummary | null;
  images?: RawTestimonyImage[];
};

type GetMyTestimoniesResponse = {
  message?: string;
  data?: RawTestimony[];
};

type UpsertTestimonyResponse = {
  message?: string;
  data?: RawTestimony;
};

type DeleteTestimonyResponse = {
  message?: string;
};

function normalizeTestimonyImage(image: RawTestimonyImage): TestimonyImage {
  return {
    id: image.id,
    testimony_id: image.testimony_id,
    image_path: image.image_path,
    image_url: image.image_url ?? null,
    created_at: image.created_at,
    updated_at: image.updated_at,
  };
}

function normalizeTestimony(testimony: RawTestimony): Testimony {
  return {
    ...testimony,
    serviceRequest: testimony.service_request ?? null,
    inspectionRequest: testimony.inspection_request ?? null,
    images: Array.isArray(testimony.images)
      ? testimony.images.map(normalizeTestimonyImage)
      : [],
  };
}

export async function getMyTestimonies() {
  const response = await apiGet<GetMyTestimoniesResponse>('/my-testimonies');

  return Array.isArray(response?.data)
    ? response.data.map(normalizeTestimony)
    : [];
}

export async function deleteTestimony(id: number) {
  const response = await apiDelete<DeleteTestimonyResponse>(`/testimonies/${id}`);

  return {
    message: response?.message || 'Testimony deleted successfully.',
  };
}

export type TestimonyFormPayload = {
  serviceRequestId?: number | null;
  inspectionRequestId?: number | null;
  rating: number;
  title?: string;
  message: string;
  newImages?: Array<{
    uri: string;
    type?: string | null;
    name?: string | null;
  }>;
  removeImageIds?: number[];
};

function normalizeLinkedRequestSelection(payload: TestimonyFormPayload) {
  if (payload.serviceRequestId) {
    return {
      serviceRequestId: payload.serviceRequestId,
      inspectionRequestId: null,
    };
  }

  if (payload.inspectionRequestId) {
    return {
      serviceRequestId: null,
      inspectionRequestId: payload.inspectionRequestId,
    };
  }

  return {
    serviceRequestId: null,
    inspectionRequestId: null,
  };
}

function appendOptionalFormField(
  formData: FormData,
  key: string,
  value?: string | number | null,
) {
  if (value === undefined || value === null || value === '') {
    return;
  }

  formData.append(key, String(value));
}

function getCreateOrUpdateDebugSnapshot(payload: TestimonyFormPayload) {
  return {
    selected_service_request_id: payload.serviceRequestId ?? null,
    selected_inspection_request_id: payload.inspectionRequestId ?? null,
    rating: payload.rating,
    title_type: typeof payload.title,
    title_value: payload.title ?? null,
    message_type: typeof payload.message,
    message_value: payload.message ?? null,
    images_array_length: Array.isArray(payload.newImages)
      ? payload.newImages.length
      : 0,
    remove_image_ids_length: Array.isArray(payload.removeImageIds)
      ? payload.removeImageIds.length
      : 0,
    form_data_exists: typeof FormData !== 'undefined',
    api_post_form_type: typeof apiPostForm,
  };
}

function buildTestimonyFormData(
  payload: TestimonyFormPayload,
  options?: {methodOverride?: 'PUT'},
) {
  console.log('buildTestimonyFormData input:', getCreateOrUpdateDebugSnapshot(payload));

  const formData = new FormData();
  const normalizedLinkedRequest = normalizeLinkedRequestSelection(payload);
  const removeImageIds = Array.isArray(payload.removeImageIds)
    ? payload.removeImageIds
    : [];
  const newImages = Array.isArray(payload.newImages) ? payload.newImages : [];
  const normalizedTitle =
    typeof payload.title === 'string' ? payload.title.trim() : '';
  const normalizedMessage =
    typeof payload.message === 'string' ? payload.message.trim() : '';

  console.log('buildTestimonyFormData instance:', {
    form_data_instance_exists: !!formData,
    form_data_append_type: typeof (formData as any)?.append,
    form_data_has_append: typeof (formData as any)?.append === 'function',
    method_override: options?.methodOverride ?? null,
  });

  if (options?.methodOverride) {
    appendOptionalFormField(formData, '_method', options.methodOverride);
  }

  appendOptionalFormField(
    formData,
    'service_request_id',
    normalizedLinkedRequest.serviceRequestId,
  );
  appendOptionalFormField(
    formData,
    'inspection_request_id',
    normalizedLinkedRequest.inspectionRequestId,
  );
  appendOptionalFormField(formData, 'rating', payload.rating);
  appendOptionalFormField(formData, 'title', normalizedTitle || null);
  appendOptionalFormField(formData, 'message', normalizedMessage);

  removeImageIds.forEach(imageId => {
    appendOptionalFormField(formData, 'remove_image_ids[]', imageId);
  });

  newImages.forEach((image, index) => {
    if (!image.uri) {
      return;
    }

    const fallbackName = `testimony-image-${Date.now()}-${index}.jpg`;

    formData.append('images[]', {
      uri: image.uri,
      type: image.type || 'image/jpeg',
      name: image.name || fallbackName,
    } as any);
  });

  return formData;
}

export async function createTestimony(payload: TestimonyFormPayload) {
  console.log('createTestimony debug before request:', getCreateOrUpdateDebugSnapshot(payload));

  const response = await apiPostForm<UpsertTestimonyResponse>(
    '/testimonies',
    buildTestimonyFormData(payload),
  );

  return {
    message: response?.message || 'Testimony submitted successfully.',
    data: response?.data ? normalizeTestimony(response.data) : null,
  };
}

export async function updateTestimony(id: number, payload: TestimonyFormPayload) {
  console.log('updateTestimony debug before request:', {
    testimony_id: id,
    ...getCreateOrUpdateDebugSnapshot(payload),
  });

  const response = await apiPostForm<UpsertTestimonyResponse>(
    `/testimonies/${id}`,
    buildTestimonyFormData(payload, {methodOverride: 'PUT'}),
  );

  return {
    message: response?.message || 'Testimony updated successfully.',
    data: response?.data ? normalizeTestimony(response.data) : null,
  };
}
