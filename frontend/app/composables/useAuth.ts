import type { ApiDataEnvelope } from '~/types/api/common';
import type { AuthLoginResultDto, AuthResultDto, AuthTwoFactorChallengeDto, AuthUserDto } from '~/types/api/private';

type LoginPayload = {
  login: string;
  password: string;
  remember?: boolean;
};

type RegisterPayload = {
  name: string;
  phone?: string;
  email: string;
  password: string;
  password_confirmation: string;
  privacy: boolean;
  notifications: boolean;
};

type TwoFactorVerifyPayload = {
  challenge_id: string;
  code: string;
};

const isTwoFactorChallenge = (payload: AuthLoginResultDto): payload is AuthTwoFactorChallengeDto => {
  return 'requiresTwoFactor' in payload && payload.requiresTwoFactor === true;
};

export const useAuth = () => {
  const api = useApi();
  const token = useCookie<string | null>('rd_auth_token', {
    default: () => null,
    sameSite: 'lax',
  });
  const user = useState<AuthUserDto | null>('rd-auth-user', () => null);

  const authHeaders = (): HeadersInit => {
    if (!token.value) {
      return {};
    }

    return {
      Authorization: `Bearer ${token.value}`,
    };
  };

  const applyAuth = (payload: AuthResultDto): void => {
    token.value = payload.token;
    user.value = payload.user;
  };

  const login = async (payload: LoginPayload): Promise<AuthUserDto | AuthTwoFactorChallengeDto> => {
    const response = await api.request<ApiDataEnvelope<AuthLoginResultDto>>('/auth/login', 'POST', {
      body: payload,
    });

    if (isTwoFactorChallenge(response.data)) {
      return response.data;
    }

    applyAuth(response.data);

    return response.data.user;
  };

  const verifyTwoFactor = async (payload: TwoFactorVerifyPayload): Promise<AuthUserDto> => {
    const response = await api.request<ApiDataEnvelope<AuthResultDto>>('/auth/2fa/verify', 'POST', {
      body: payload,
    });
    applyAuth(response.data);

    return response.data.user;
  };

  const register = async (payload: RegisterPayload): Promise<AuthUserDto> => {
    const response = await api.request<ApiDataEnvelope<AuthResultDto>>('/auth/register', 'POST', {
      body: payload,
    });
    applyAuth(response.data);

    return response.data.user;
  };

  const fetchMe = async (): Promise<AuthUserDto | null> => {
    if (!token.value) {
      user.value = null;

      return null;
    }

    try {
      const response = await api.get<ApiDataEnvelope<AuthUserDto>>('/auth/me', {
        headers: authHeaders(),
      });
      user.value = response.data;

      return response.data;
    } catch {
      token.value = null;
      user.value = null;

      return null;
    }
  };

  const logout = async (): Promise<void> => {
    if (token.value) {
      await api.request('/auth/logout', 'POST', {
        headers: authHeaders(),
      }).catch(() => undefined);
    }

    token.value = null;
    user.value = null;
    await navigateTo('/login');
  };

  return {
    authHeaders,
    fetchMe,
    login,
    logout,
    register,
    token,
    user,
    verifyTwoFactor,
  };
};
