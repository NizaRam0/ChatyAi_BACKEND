// Validation helpers for upload form rules.
import {
  ALLOWED_IMAGE_TYPES,
  MAX_FILE_SIZE_BYTES,
  MIN_IMAGE_WIDTH,
  MIN_IMAGE_HEIGHT,
  MAX_IMAGE_WIDTH,
  MAX_IMAGE_HEIGHT,
} from './constants'

// Validates a selected file against image type and file size constraints.
export function validateImageFile(file) {
  if (!file) {
    return {
      valid: false,
      message: 'Please choose an image before submitting.',
    }
  }

  if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
    return {
      valid: false,
      message: 'Invalid file type. Use JPEG or PNG only.',
    }
  }

  if (file.size > MAX_FILE_SIZE_BYTES) {
    return {
      valid: false,
      message: 'Image is too large. Max allowed size is 10 MB.',
    }
  }

  return {
    valid: true,
    message: '',
  }
}

// Loads image dimensions from local object URL for client-side dimension validation.
function getImageDimensions(file) {
  return new Promise((resolve, reject) => {
    const objectUrl = URL.createObjectURL(file)
    const image = new Image()

    image.onload = () => {
      const dimensions = {
        width: image.width,
        height: image.height,
      }
      URL.revokeObjectURL(objectUrl)
      resolve(dimensions)
    }

    image.onerror = () => {
      URL.revokeObjectURL(objectUrl)
      reject(new Error('Could not read image dimensions.'))
    }

    image.src = objectUrl
  })
}

// Asynchronously validates image dimensions against backend constraints.
export async function validateImageDimensions(file) {
  try {
    const { width, height } = await getImageDimensions(file)

    const widthValid = width >= MIN_IMAGE_WIDTH && width <= MAX_IMAGE_WIDTH
    const heightValid = height >= MIN_IMAGE_HEIGHT && height <= MAX_IMAGE_HEIGHT

    if (!widthValid || !heightValid) {
      return {
        valid: false,
        message: `Image dimensions must be between ${MIN_IMAGE_WIDTH}x${MIN_IMAGE_HEIGHT} and ${MAX_IMAGE_WIDTH}x${MAX_IMAGE_HEIGHT}px.`,
      }
    }

    return {
      valid: true,
      message: '',
    }
  } catch {
    return {
      valid: false,
      message: 'The selected file could not be processed as a valid image.',
    }
  }
}

// Returns true when user typed a non-empty token.
export function hasAuthToken(token) {
  return typeof token === 'string' && token.trim().length > 0
}
