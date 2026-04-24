import {apiPostForm} from './api';

export type ProfilePictureUser = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
  address?: string | null;
  contact_number?: string | null;
  profile_picture?: string | null;
};

export type ProfilePictureAsset = {
  uri: string;
  type?: string | null;
  name?: string | null;
};

type UploadProfilePictureResponse = {
  message: string;
  user: ProfilePictureUser;
};

function buildProfilePictureFormData(asset: ProfilePictureAsset) {
  const formData = new FormData();

  formData.append('profile_picture', {
    uri: asset.uri,
    type: asset.type || 'image/jpeg',
    name: asset.name || `profile-picture-${Date.now()}.jpg`,
  } as any);

  return formData;
}

export function uploadProfilePicture(asset: ProfilePictureAsset) {
  return apiPostForm<UploadProfilePictureResponse>(
    '/user/profile-picture',
    buildProfilePictureFormData(asset),
  );
}
