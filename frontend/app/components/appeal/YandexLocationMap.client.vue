<script setup lang="ts">
type YandexCoordinates = [number, number];

type YandexGeocodeOptions = {
  results?: number;
  kind?: 'house' | 'street' | 'metro' | 'district' | 'locality';
};

type YandexGeoObject = {
  geometry: {
    getCoordinates: () => YandexCoordinates;
  };
  getAddressLine?: () => string;
  properties: {
    get: (name: string) => unknown;
  };
};

type YandexGeoObjectCollection = {
  get: (index: number) => YandexGeoObject | null;
};

type YandexGeocodeResult = {
  geoObjects: YandexGeoObjectCollection;
};

type YandexPromise<T> = {
  then: (resolve: (value: T) => void, reject?: (error: unknown) => void) => void;
};

type YandexMapEvent = {
  get: (name: 'coords') => YandexCoordinates;
};

type YandexMap = {
  destroy: () => void;
  events: {
    add: (name: 'click', handler: (event: YandexMapEvent) => void) => void;
  };
  geoObjects: {
    add: (geoObject: YandexPlacemark) => void;
  };
  getZoom: () => number;
  setCenter: (coordinates: YandexCoordinates, zoom?: number, options?: Record<string, unknown>) => void;
  setZoom: (zoom: number, options?: Record<string, unknown>) => void;
};

type YandexPlacemark = {
  geometry: {
    setCoordinates: (coordinates: YandexCoordinates) => void;
  };
  properties: {
    set: (name: string, value: string) => void;
  };
};

type YandexMaps = {
  Map: new (
    container: HTMLElement,
    state: { center: YandexCoordinates; zoom: number; controls: string[] },
    options?: Record<string, unknown>,
  ) => YandexMap;
  Placemark: new (
    coordinates: YandexCoordinates,
    properties?: Record<string, unknown>,
    options?: Record<string, unknown>,
  ) => YandexPlacemark;
  geocode: (request: string | YandexCoordinates, options?: YandexGeocodeOptions) => YandexPromise<YandexGeocodeResult>;
  ready: (callback: () => void) => void;
};

declare global {
  interface Window {
    ymaps?: YandexMaps;
    yandexMapsApiPromise?: Promise<YandexMaps>;
  }
}

const props = defineProps<{
  modelValue: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const config = useRuntimeConfig();
const mapContainer = ref<HTMLElement | null>(null);
const mapStatus = ref<'idle' | 'loading' | 'ready' | 'missing-key' | 'error'>('idle');
const mapMessage = ref('');
const mapsApi = shallowRef<YandexMaps | null>(null);
const mapInstance = shallowRef<YandexMap | null>(null);
const placemark = shallowRef<YandexPlacemark | null>(null);
const geocoding = ref(false);
const locating = ref(false);
const programmaticAddress = ref('');

const defaultCoordinates: YandexCoordinates = [55.751244, 37.618423];
const yandexMapsScriptId = 'yandex-maps-js-api';
let addressSearchTimer: ReturnType<typeof setTimeout> | null = null;
let geocodeRequestCounter = 0;
let destroyed = false;

const apiKey = computed(() => {
  return String(config.public.yandexMapsApiKey || '').trim();
});

const canUseMap = computed(() => mapStatus.value === 'ready' && mapInstance.value !== null);
const statusText = computed(() => {
  if (geocoding.value) {
    return 'Ищем адрес на карте...';
  }

  if (locating.value) {
    return 'Определяем текущее местоположение...';
  }

  return mapMessage.value;
});

const loadYandexMaps = (key: string): Promise<YandexMaps> => {
  if (window.ymaps) {
    return new Promise((resolve) => {
      window.ymaps?.ready(() => {
        resolve(window.ymaps as YandexMaps);
      });
    });
  }

  if (window.yandexMapsApiPromise) {
    return window.yandexMapsApiPromise;
  }

  window.yandexMapsApiPromise = new Promise((resolve, reject) => {
    const handleReady = (): void => {
      if (!window.ymaps) {
        reject(new Error('Yandex Maps API is unavailable.'));

        return;
      }

      window.ymaps.ready(() => {
        resolve(window.ymaps as YandexMaps);
      });
    };

    const existingScript = document.getElementById(yandexMapsScriptId) as HTMLScriptElement | null;

    if (existingScript) {
      existingScript.addEventListener('load', handleReady, { once: true });
      existingScript.addEventListener('error', () => reject(new Error('Yandex Maps API loading failed.')), { once: true });

      return;
    }

    const script = document.createElement('script');
    const params = new URLSearchParams({
      apikey: key,
      lang: 'ru_RU',
    });

    script.id = yandexMapsScriptId;
    script.src = `https://api-maps.yandex.ru/2.1/?${params.toString()}`;
    script.async = true;
    script.type = 'text/javascript';
    script.addEventListener('load', handleReady, { once: true });
    script.addEventListener('error', () => reject(new Error('Yandex Maps API loading failed.')), { once: true });

    document.head.appendChild(script);
  });

  return window.yandexMapsApiPromise;
};

const readAddressLine = (geoObject: YandexGeoObject): string => {
  const addressLine = geoObject.getAddressLine?.();

  if (addressLine && addressLine.trim() !== '') {
    return addressLine.trim();
  }

  const name = geoObject.properties.get('name');

  if (typeof name === 'string') {
    return name.trim();
  }

  return '';
};

const runGeocode = (request: string | YandexCoordinates, options: YandexGeocodeOptions): Promise<YandexGeocodeResult> => {
  return new Promise((resolve, reject) => {
    if (!mapsApi.value) {
      reject(new Error('Yandex Maps API is unavailable.'));

      return;
    }

    mapsApi.value.geocode(request, options).then(resolve, reject);
  });
};

const firstGeoObject = (result: YandexGeocodeResult): YandexGeoObject | null => {
  return result.geoObjects.get(0);
};

const setPlacemark = (coordinates: YandexCoordinates, caption: string): void => {
  placemark.value?.geometry.setCoordinates(coordinates);
  placemark.value?.properties.set('iconCaption', caption);
  mapInstance.value?.setCenter(coordinates, 16, { duration: 300 });
};

const updateAddress = (address: string): void => {
  programmaticAddress.value = address;
  emit('update:modelValue', address);
};

const geocodeAddress = async (address: string): Promise<void> => {
  if (!mapsApi.value || address === '') {
    return;
  }

  const requestId = ++geocodeRequestCounter;
  geocoding.value = true;
  mapMessage.value = '';

  try {
    const result = await runGeocode(address, { results: 1 });

    if (requestId !== geocodeRequestCounter) {
      return;
    }

    const geoObject = firstGeoObject(result);

    if (!geoObject) {
      mapMessage.value = 'Адрес не найден. Можно выбрать точку на карте вручную.';

      return;
    }

    const coordinates = geoObject.geometry.getCoordinates();

    setPlacemark(coordinates, address);
  } catch {
    mapMessage.value = 'Не удалось найти адрес на карте. Попробуйте уточнить запрос.';
  } finally {
    if (requestId === geocodeRequestCounter) {
      geocoding.value = false;
    }
  }
};

const reverseGeocodeCoordinates = async (coordinates: YandexCoordinates): Promise<void> => {
  if (!mapsApi.value) {
    return;
  }

  const requestId = ++geocodeRequestCounter;
  geocoding.value = true;
  mapMessage.value = '';

  try {
    const result = await runGeocode(coordinates, { results: 1, kind: 'house' });

    if (requestId !== geocodeRequestCounter) {
      return;
    }

    const geoObject = firstGeoObject(result);
    const address = geoObject ? readAddressLine(geoObject) : '';

    if (address === '') {
      mapMessage.value = 'Точка выбрана. Уточните адрес вручную.';

      return;
    }

    setPlacemark(coordinates, address);
    updateAddress(address);
  } catch {
    mapMessage.value = 'Точка выбрана. Не удалось получить адрес автоматически.';
  } finally {
    if (requestId === geocodeRequestCounter) {
      geocoding.value = false;
    }
  }
};

const handleMapClick = (event: YandexMapEvent): void => {
  const coordinates = event.get('coords');

  setPlacemark(coordinates, 'Выбранная точка');
  void reverseGeocodeCoordinates(coordinates);
};

const initializeMap = (ymaps: YandexMaps): void => {
  if (!mapContainer.value) {
    throw new Error('Map container is unavailable.');
  }

  const map = new ymaps.Map(
    mapContainer.value,
    {
      center: defaultCoordinates,
      controls: [],
      zoom: 12,
    },
    {
      suppressMapOpenBlock: true,
    },
  );
  const marker = new ymaps.Placemark(
    defaultCoordinates,
    {
      iconCaption: 'Место происшествия',
    },
    {
      preset: 'islands#blueCircleDotIconWithCaption',
    },
  );

  map.geoObjects.add(marker);
  map.events.add('click', handleMapClick);

  mapInstance.value = map;
  placemark.value = marker;
};

const changeZoom = (delta: number): void => {
  const map = mapInstance.value;

  if (!map) {
    return;
  }

  map.setZoom(map.getZoom() + delta, { duration: 150 });
};

const useCurrentLocation = (): void => {
  if (!navigator.geolocation) {
    mapMessage.value = 'Браузер не поддерживает определение местоположения.';

    return;
  }

  locating.value = true;
  mapMessage.value = '';

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const coordinates: YandexCoordinates = [
        position.coords.latitude,
        position.coords.longitude,
      ];

      locating.value = false;
      setPlacemark(coordinates, 'Текущее местоположение');
      void reverseGeocodeCoordinates(coordinates);
    },
    () => {
      locating.value = false;
      mapMessage.value = 'Не удалось получить текущее местоположение.';
    },
    {
      enableHighAccuracy: true,
      maximumAge: 60000,
      timeout: 8000,
    },
  );
};

watch(
  () => props.modelValue,
  (value) => {
    const address = value.trim();

    if (address === programmaticAddress.value) {
      programmaticAddress.value = '';

      return;
    }

    if (!canUseMap.value || address === '') {
      return;
    }

    if (addressSearchTimer) {
      clearTimeout(addressSearchTimer);
    }

    addressSearchTimer = setTimeout(() => {
      void geocodeAddress(address);
    }, 700);
  },
);

onMounted(async () => {
  if (apiKey.value === '') {
    mapStatus.value = 'missing-key';

    return;
  }

  mapStatus.value = 'loading';

  try {
    const ymaps = await loadYandexMaps(apiKey.value);

    if (destroyed) {
      return;
    }

    mapsApi.value = ymaps;
    initializeMap(ymaps);
    mapStatus.value = 'ready';

    if (props.modelValue.trim() !== '') {
      await geocodeAddress(props.modelValue.trim());
    }
  } catch {
    mapStatus.value = 'error';
  }
});

onBeforeUnmount(() => {
  destroyed = true;

  if (addressSearchTimer) {
    clearTimeout(addressSearchTimer);
  }

  mapInstance.value?.destroy();
});
</script>

<template>
  <div class="yandex-location">
    <div v-if="mapStatus === 'missing-key'" class="map-unavailable" role="status">
      <LayoutAppIcon name="pin" />
      <div>
        <strong>Карта временно недоступна</strong>
        <span>Адрес можно указать вручную.</span>
      </div>
    </div>

    <div v-else class="yandex-location-map" aria-label="Яндекс.Карта с выбранной точкой">
      <div ref="mapContainer" class="yandex-location-map__canvas" />

      <div v-if="mapStatus === 'loading'" class="yandex-location-map__overlay" role="status">
        <span>Загружаем карту...</span>
      </div>

      <div v-if="mapStatus === 'error'" class="yandex-location-map__overlay" role="status">
        <div class="yandex-location-map__state">
          <LayoutAppIcon name="pin" />
          <div>
            <strong>Карта не загрузилась</strong>
            <span>Адрес можно указать вручную.</span>
          </div>
        </div>
      </div>

      <div v-if="mapStatus === 'ready'" class="map-controls map-controls--yandex" aria-label="Управление картой">
        <button type="button" aria-label="Увеличить масштаб" @click="changeZoom(1)">
          +
        </button>
        <button type="button" aria-label="Уменьшить масштаб" @click="changeZoom(-1)">
          -
        </button>
        <button type="button" aria-label="Определить текущее местоположение" :disabled="locating" @click="useCurrentLocation">
          <LayoutAppIcon name="pin" />
        </button>
      </div>
    </div>

    <p v-if="statusText" class="map-status">
      {{ statusText }}
    </p>
  </div>
</template>
