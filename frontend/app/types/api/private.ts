export type AuthUserDto = {
  id: string;
  name: string;
  email: string;
  phone: string | null;
  notificationsEnabled: boolean;
  avatarUrl: string | null;
};

export type AuthResultDto = {
  token: string;
  tokenType: 'Bearer';
  user: AuthUserDto;
};

export type AppealDraftDto = {
  id: string;
  guestToken: string | null;
  status: 'draft' | 'pending_moderation';
  category: string | null;
  submissionType: string | null;
  title: string | null;
  description: string | null;
  urgency: string | null;
  location: string | null;
  contactVisibility: string | null;
  contactName: string | null;
  contactEmail: string | null;
  contactPhone: string | null;
  submittedAt: string | null;
  attachments: Array<{
    id: string;
    kind: string;
    originalName: string;
    mimeType: string;
    size: number;
  }>;
};

export type DashboardListDto<TItem> = {
  items: TItem[];
};
