// resources/js/composables/usePermissionLabel.js

const resourceLabels = {
  'organization':  '契約先',
  'member':        '会員',
  'invoice':       '請求書',
  'stripe':        'Stripe支払い',
  'tenant':        'テナント',
  'license-fee':   'ライセンス料',
  'role':          'ロール',
  'permission':    '権限',
  'admin':         '管理者',
  'user':          'ユーザー',
}

const actionLabels = {
  view: '閲覧',
  edit: '編集',
}

/**
 * permission の name（例: "organization.view"）を日本語ラベル（例: "組織：閲覧"）に変換する
 * 辞書に存在しない resource / action は name のまま表示する（フォールバック）
 */
export function usePermissionLabel() {
  const getPermissionLabel = (name) => {
    if (!name) return ''

    const [resource, action] = name.split('.')
    const resourceLabel = resourceLabels[resource] ?? resource
    const actionLabel = actionLabels[action] ?? action

    return `${resourceLabel}：${actionLabel}`
  }

  return { getPermissionLabel }
}
