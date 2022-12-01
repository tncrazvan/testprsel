export type ServerResponse<T> = {
  data: T;
  message: string;
  code: number;
};
