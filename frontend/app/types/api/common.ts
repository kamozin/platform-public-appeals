export type ApiDataEnvelope<TData> = {
  data: TData;
};

export type ApiErrorEnvelope = {
  error: {
    code: string;
    message: string;
    details: Record<string, unknown>;
    trace_id: string;
  };
};
