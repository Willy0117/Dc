import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export type ContractStatus = 0 | 1 | 2 | 3
export type MemberAddressType = 1 | 2
export type MemberStatusId = 1 | 2 | 3

// organizations テーブル
export interface Organization {
  id?: number
  name: string
  abbr: string | null
  url: string | null
  contract_no: number | null
  contract_date: string | null
  contract_status: ContractStatus
  payment_method: 1 | 2 | null
}

// organization_addresses テーブル（Controller に合わせてキー別）
export interface OrganizationAddress {
  name: string | null
  postal_code: string | null
  address1: string | null
  address2: string | null
  address3: string | null
  tel: string | null
  fax: string | null
  email: string | null
}

// member_addresses テーブル
export interface MemberAddress {
  type: MemberAddressType
  postal_code: string | null
  address1: string | null
  address2: string | null
  address3: string | null
  tel: string | null
  fax: string | null
}

// members テーブル
export interface Member {
  id?: number
  organization_id: number | null
  member_number: string | null
  position: string | null
  last_name: string
  first_name: string
  last_name_kana: string | null
  first_name_kana: string | null
  gender: string | null
  birthdate: string | null
  tel: string | null
  mobile: string | null
  fax: string | null
  email: string | null
  personal_email: string | null
  status_id: MemberStatusId
  member_type: string | null
  joined_at: string | null
  withdrawn_at: string | null
  addresses: MemberAddress[]
}

// Edit.vue の Inertia props（Controller の edit() に合わせた構造）
export interface OrganizationEditProps {
  organization: Organization | null
  location_address: OrganizationAddress | null
  shipping_address: OrganizationAddress | null
  billing_address: OrganizationAddress | null
  members: Member[]
  filters: Record<string, string>
  errors?: Record<string, string>
}

// OrganizationForm / store・update に POST する構造
export interface OrganizationFormData {
  organization: Organization
  location_address: OrganizationAddress
  shipping_address: OrganizationAddress
  billing_address: OrganizationAddress
  members: Member[]
}

// ─── Member 関連 ───────────────────────────────────────

export interface MemberEducation {
  id?: number
  school_name: string | null
  faculty: string | null
  graduated_at: string | null
}

export interface MemberDegree {
  id?: number
  degree: string | null
  obtained_at: string | null
}

export interface MemberRole {
  id?: number
  role: string | null
  started_at: string | null
  ended_at: string | null
}

export interface MemberCommittee {
  id?: number
  committee: string | null
  started_at: string | null
  ended_at: string | null
}

// member_addresses（自宅/送付先 キー別）
export interface MemberAddressItem {
  postal_code: string | null
  address1: string | null
  address2: string | null
  address3: string | null
  tel: string | null
  fax: string | null
}

// Controller の edit() が返す props
export interface MemberEditProps {
  member: Member | null
  organization: { id: number; name: string; abbr: string | null } | null
  home_address: MemberAddressItem | null
  shipping_address: MemberAddressItem | null
  education: MemberEducation | null
  degrees: MemberDegree[]
  roles: MemberRole[]
  committees: MemberCommittee[]
  filters: Record<string, string>
}

// store / update に POST する構造
export interface MemberFormData {
  member: Omit<Member, 'addresses'>
  home_address: MemberAddressItem
  shipping_address: MemberAddressItem
  education: MemberEducation
  degrees: MemberDegree[]
  roles: MemberRole[]
  committees: MemberCommittee[]
}