export interface Municipality {
  id: number;
  name: string;
  slug: string;
  domain: string | null;
  logo: string | null;
  primary_color: string;
  secondary_color: string;
  status: string;
  settings: Record<string, unknown>;
}
