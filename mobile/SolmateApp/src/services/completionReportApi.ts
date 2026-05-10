export type CompletionReportStatus =
  | 'pending'
  | 'approved'
  | string;

export type CompletionReportUser = {
  id: number;
  name?: string | null;
  email?: string | null;
};

export type CompletionReportPhoto = {
  id: number;
  completion_report_id: number;
  image_path: string;
  image_url?: string | null;
};

export type CompletionReport = {
  id: number;
  service_request_id?: number | null;
  inspection_request_id?: number | null;
  technician_id?: number | null;
  approved_by?: number | null;
  report_text: string;
  findings?: string | null;
  recommendations?: string | null;
  status: CompletionReportStatus;
  completed_at?: string | null;
  submitted_at?: string | null;
  approved_at?: string | null;
  technician?: CompletionReportUser | null;
  approver?: CompletionReportUser | null;
  photos?: CompletionReportPhoto[];
};

export type CompletionReportPayload = {
  report_text: string;
  findings?: string;
  recommendations?: string;
  completed_at: string;
};

export type LocalCompletionPhoto = {
  uri: string;
  type: string;
  name: string | null;
};

export type ServiceCompletionReportPayload = {
  report_text: string;
  completion_photos: LocalCompletionPhoto[];
  completed_at: string;
};
