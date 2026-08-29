import { reactive, computed } from 'vue'
import type {
  MemberEditProps,
  MemberFormData,
  MemberAddressItem,
  MemberEducation,
  MemberDegree,
  MemberRole,
  MemberCommittee,
} from '@/types'

export const MEMBER_STATUS_OPTIONS = [
  { value: 1 as const, label: '通常', color: 'teal'  },
  { value: 2 as const, label: '休会', color: 'amber' },
  { value: 3 as const, label: '退会', color: 'gray'  },
] as const

export const GENDER_OPTIONS = [
  { value: 'male',   label: '男性' },
  { value: 'female', label: '女性' },
  { value: 'other',  label: 'その他' },
]

function makeAddress(): MemberAddressItem {
  return { postal_code: null, address1: null, address2: null, address3: null, tel: null, fax: null }
}

function makeEducation(): MemberEducation {
  return { school_name: null, faculty: null, graduated_at: null }
}

export function makeDegree(): MemberDegree {
  return { degree: null, obtained_at: null }
}

export function makeRole(): MemberRole {
  return { role: null, started_at: null, ended_at: null }
}

export function makeCommittee(): MemberCommittee {
  return { committee: null, started_at: null, ended_at: null }
}

function makeFormFromProps(props: MemberEditProps): MemberFormData {
  return {
    member: {
      id:               props.member?.id,
      organization_id:  props.member?.organization_id ?? null,
      member_number:    props.member?.member_number   ?? null,
      position:         props.member?.position        ?? null,
      last_name:        props.member?.last_name       ?? '',
      first_name:       props.member?.first_name      ?? '',
      last_name_kana:   props.member?.last_name_kana  ?? null,
      first_name_kana:  props.member?.first_name_kana ?? null,
      gender:           props.member?.gender          ?? null,
      birthdate:        props.member?.birthdate       ?? null,
      tel:              props.member?.tel             ?? null,
      mobile:           props.member?.mobile          ?? null,
      fax:              props.member?.fax             ?? null,
      email:            props.member?.email           ?? null,
      personal_email:   props.member?.personal_email  ?? null,
      status_id:        props.member?.status_id       ?? 1,
      member_type:      props.member?.member_type     ?? null,
      joined_at:        props.member?.joined_at       ?? null,
      withdrawn_at:     props.member?.withdrawn_at    ?? null,
    },
    home_address:     props.home_address     ?? makeAddress(),
    shipping_address: props.shipping_address ?? makeAddress(),
    education:        props.education        ?? makeEducation(),
    degrees:          props.degrees?.length  ? props.degrees    : [makeDegree()],
    roles:            props.roles?.length    ? props.roles      : [makeRole()],
    committees:       props.committees?.length ? props.committees : [makeCommittee()],
  }
}

export function useMemberForm(props: MemberEditProps) {
  const form = reactive<MemberFormData>(makeFormFromProps(props))

  const isValid = computed(() =>
    (form.member.last_name ?? '').trim() !== '' &&
    (form.member.first_name ?? '').trim() !== '' &&
    (form.member.email ?? '').trim() !== ''
  )

  function copyHomeToShipping() {
    Object.assign(form.shipping_address, { ...form.home_address })
  }

  // degrees
  function addDegree()              { form.degrees.push(makeDegree()) }
  function removeDegree(i: number)  { form.degrees.splice(i, 1) }

  // roles
  function addRole()                { form.roles.push(makeRole()) }
  function removeRole(i: number)    { form.roles.splice(i, 1) }

  // committees
  function addCommittee()              { form.committees.push(makeCommittee()) }
  function removeCommittee(i: number)  { form.committees.splice(i, 1) }

  return {
    form,
    isValid,
    copyHomeToShipping,
    addDegree, removeDegree,
    addRole, removeRole,
    addCommittee, removeCommittee,
  }
}
