declare module 'react-native-config' {
  export interface NativeConfig {
    [name: string]: string | undefined;
    API_BASE_URL?: string;
    GEMINI_API_KEY?: string;
  }

  export const Config: NativeConfig;
  export default Config;
}
