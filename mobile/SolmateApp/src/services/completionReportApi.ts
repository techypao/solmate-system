export type CompletionReportStatus =
  | 'pending'
  | 'approved'
  | string;

export type CompletionReportUser = {
  id: number;
  name?: string | null;
  email?: string | null;
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
};

export type CompletionReportPayload = {
  report_text: string;
  findings?: string;
  recommendations?: string;
  completed_at: string;
};
