import Toast from '../components/Toast'

export function addCallout(message, label) {
  switch (label) {
    case 'success':
      Toast.success(message)
      break
    case 'danger':
    case 'error':
      Toast.error(message)
      break
    case 'warning':
      Toast.warning(message)
      break
    case 'info':
      Toast.info(message)
      break
  }
}
