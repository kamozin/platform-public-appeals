export type AuthUserDto = {
  id: string;
  name: string;
  email: string;
  phone: string | null;
  notificationsEnabled: boolean;
  isAdmin: boolean;
  avatarUrl: string | null;
  emailTwoFactorEnabled: boolean;
};

export type AuthResultDto = {
  token: string;
  tokenType: 'Bearer';
  user: AuthUserDto;
};

export type AuthTwoFactorChallengeDto = {
  requiresTwoFactor: true;
  challengeId: string;
  expiresAt: string | null;
  maskedTarget: string;
  devCode?: string;
};

export type AuthLoginResultDto = AuthResultDto | AuthTwoFactorChallengeDto;

export type AppealDraftDto = {
  id: string;
  guestToken: string | null;
  status: 'draft' | 'pending_moderation' | 'needs_changes' | 'rejected' | 'approved';
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
  moderatedAt: string | null;
  moderationNote: string | null;
  rejectionReason: string | null;
  publicAppealId: string | null;
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
