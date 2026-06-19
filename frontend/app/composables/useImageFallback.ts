const defaultFallbackImage = '/assets/hero-civic-flag.png';

export const resolveImageUrl = (
  imageUrl: string | null | undefined,
  fallbackImage: string = defaultFallbackImage,
): string => {
  if (!imageUrl?.trim()) {
    return fallbackImage;
  }

  return imageUrl;
};

export const useImageFallback = (fallbackImage: string = defaultFallbackImage) => {
  const handleImageError = (event: Event): void => {
    if (!(event.target instanceof HTMLImageElement)) {
      return;
    }

    if (event.target.dataset.fallbackApplied === 'true') {
      return;
    }

    event.target.dataset.fallbackApplied = 'true';
    event.target.src = fallbackImage;
  };

  return {
    fallbackImage,
    handleImageError,
    resolveImageUrl: (imageUrl: string | null | undefined): string => resolveImageUrl(imageUrl, fallbackImage),
  };
};
