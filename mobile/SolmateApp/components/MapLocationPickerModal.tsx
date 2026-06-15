import React, {useEffect, useMemo, useRef, useState} from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Modal,
  PermissionsAndroid,
  Platform,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  useWindowDimensions,
  View,
} from 'react-native';
import WebView, {WebViewMessageEvent} from 'react-native-webview';

const NAVY = '#123A5A';
const GOLD = '#F4D000';
const MUTED = '#5E7288';
const CARD = '#ffffff';

const DEFAULT_COORDS = {
  latitude: 14.2691,
  longitude: 121.4113,
  zoom: 10,
};

type Coordinates = {
  latitude: number;
  longitude: number;
  zoom?: number;
};

type FeedbackState =
  | {
      message: string;
      tone: 'error' | 'info';
    }
  | null;

type MapLocationPickerModalProps = {
  visible: boolean;
  initialLatitude?: number | null;
  initialLongitude?: number | null;
  title?: string;
  subtitle?: string;
  permissionMessage?: string;
  onCancel: () => void;
  onConfirm: (payload: {
    latitude: number;
    longitude: number;
    resolvedAddress: string | null;
    reverseGeocodeFailed: boolean;
  }) => void;
};

type NominatimSearchResult = {
  lat?: string;
  lon?: string;
};

type NominatimReverseResult = {
  display_name?: string;
};

type MapWebMessage =
  | {type: 'ready'}
  | {type: 'markerChanged'; latitude: number; longitude: number}
  | {type: 'currentLocation'; latitude: number; longitude: number}
  | {
      type: 'currentLocationError';
      code?: number | string;
      message?: string;
    };

function getInitialCoords(
  latitude?: number | null,
  longitude?: number | null,
): Coordinates {
  if (
    typeof latitude === 'number' &&
    Number.isFinite(latitude) &&
    typeof longitude === 'number' &&
    Number.isFinite(longitude)
  ) {
    return {
      latitude,
      longitude,
      zoom: 16,
    };
  }

  return DEFAULT_COORDS;
}

function getPreferredLanguage() {
  try {
    return Intl.DateTimeFormat().resolvedOptions().locale || 'en-PH';
  } catch {
    return 'en-PH';
  }
}

function buildNominatimUrl(path: string, params: Record<string, string>) {
  const query = new URLSearchParams(params);
  query.set('format', 'jsonv2');
  query.set('accept-language', getPreferredLanguage());

  return `https://nominatim.openstreetmap.org/${path}?${query.toString()}`;
}

async function nominatimRequest<T>(
  path: string,
  params: Record<string, string>,
): Promise<T> {
  const response = await fetch(buildNominatimUrl(path, params), {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Accept-Language': getPreferredLanguage(),
      'User-Agent': 'SolmateApp/0.0.1',
    },
  });

  if (!response.ok) {
    throw new Error('Nominatim request failed.');
  }

  return response.json() as Promise<T>;
}

function buildMapHtml(initialCoords: Coordinates) {
  const initialLatitude = JSON.stringify(initialCoords.latitude);
  const initialLongitude = JSON.stringify(initialCoords.longitude);
  const initialZoom = JSON.stringify(initialCoords.zoom || DEFAULT_COORDS.zoom);

  return `<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"
    />
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""
    />
    <style>
      html,
      body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background: #f7f9fc;
      }

      #map {
        width: 100%;
        height: 100%;
      }

      .leaflet-container {
        background: #EAF9FD;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      }

      .leaflet-control-attribution {
        font-size: 10px;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script
      src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin=""
    ></script>
    <script>
      (function () {
        var defaultCoords = {
          latitude: ${initialLatitude},
          longitude: ${initialLongitude},
          zoom: ${initialZoom},
        };
        var map = L.map('map', {
          zoomControl: true,
          attributionControl: true,
        }).setView(
          [defaultCoords.latitude, defaultCoords.longitude],
          defaultCoords.zoom
        );

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap contributors',
          maxZoom: 19,
        }).addTo(map);

        var marker = L.marker(
          [defaultCoords.latitude, defaultCoords.longitude],
          { draggable: true }
        ).addTo(map);

        function postMessage(payload) {
          if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(payload));
          }
        }

        function syncMarker(latitude, longitude, options) {
          var normalizedLat = Number(latitude);
          var normalizedLng = Number(longitude);
          var zoom = (options && options.zoom) || map.getZoom() || defaultCoords.zoom;
          var shouldCenter = !options || options.center !== false;

          marker.setLatLng([normalizedLat, normalizedLng]);

          if (shouldCenter) {
            map.setView([normalizedLat, normalizedLng], zoom, { animate: false });
          }

          postMessage({
            type: 'markerChanged',
            latitude: normalizedLat,
            longitude: normalizedLng,
          });
        }

        map.on('click', function (event) {
          syncMarker(event.latlng.lat, event.latlng.lng);
        });

        marker.on('dragend', function () {
          var latLng = marker.getLatLng();
          syncMarker(latLng.lat, latLng.lng, { center: false });
        });

        window.solmateMap = {
          invalidateMapSize: function () {
            setTimeout(function () {
              map.invalidateSize();
            }, 250);
          },
          setMarker: function (latitude, longitude, zoom) {
            syncMarker(latitude, longitude, { zoom: zoom || 16 });
          },
          requestCurrentLocation: function () {
            if (!navigator.geolocation) {
              postMessage({
                type: 'currentLocationError',
                code: 'UNAVAILABLE',
                message: 'Geolocation is not supported by this browser.',
              });
              return;
            }

            navigator.geolocation.getCurrentPosition(
              function (position) {
                var latitude = position.coords.latitude;
                var longitude = position.coords.longitude;
                syncMarker(latitude, longitude, { zoom: 16 });
                postMessage({
                  type: 'currentLocation',
                  latitude: latitude,
                  longitude: longitude,
                });
              },
              function (error) {
                postMessage({
                  type: 'currentLocationError',
                  code: error && error.code,
                  message: error && error.message,
                });
              },
              {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0,
              }
            );
          },
        };

        postMessage({ type: 'ready' });
        window.solmateMap.invalidateMapSize();
      })();
    </script>
  </body>
</html>`;
}

function injectMapCommand(command: string) {
  return `try { ${command} } catch (error) {} true;`;
}

export default function MapLocationPickerModal({
  visible,
  initialLatitude,
  initialLongitude,
  title = 'Pin Inspection Location',
  subtitle =
    'Search or move the pin to your exact inspection spot, then confirm to fill the form.',
  permissionMessage =
    'SolMate needs your location so you can pin your inspection spot on the map.',
  onCancel,
  onConfirm,
}: MapLocationPickerModalProps) {
  const {height: windowHeight, width: windowWidth} = useWindowDimensions();
  const webViewRef = useRef<WebView>(null);
  const [mapReady, setMapReady] = useState(false);
  const [webViewKey, setWebViewKey] = useState(0);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCoords, setSelectedCoords] = useState<Coordinates>(
    DEFAULT_COORDS,
  );
  const [sessionInitialCoords, setSessionInitialCoords] =
    useState<Coordinates>(DEFAULT_COORDS);
  const [feedback, setFeedback] = useState<FeedbackState>(null);
  const [searching, setSearching] = useState(false);
  const [locating, setLocating] = useState(false);
  const [confirming, setConfirming] = useState(false);

  useEffect(() => {
    if (!visible) {
      return;
    }

    const nextCoords = getInitialCoords(initialLatitude, initialLongitude);
    setSessionInitialCoords(nextCoords);
    setSelectedCoords(nextCoords);
    setSearchQuery('');
    setFeedback(null);
    setSearching(false);
    setLocating(false);
    setConfirming(false);
    setMapReady(false);
    setWebViewKey(currentKey => currentKey + 1);
  }, [initialLatitude, initialLongitude, visible]);

  const mapHtml = useMemo(
    () => buildMapHtml(sessionInitialCoords),
    [sessionInitialCoords],
  );
  const sheetMaxHeight = Math.floor(
    windowHeight * (windowWidth >= 768 ? 0.88 : 0.92),
  );
  const mapHeight = Math.round(
    Math.min(
      Math.max(windowHeight * (windowHeight < 700 ? 0.24 : 0.28), 168),
      windowWidth >= 700 ? 280 : 240,
    ),
  );

  useEffect(() => {
    if (!visible || !webViewRef.current || !mapReady) {
      return;
    }

    const timer = setTimeout(() => {
      webViewRef.current?.injectJavaScript(
        injectMapCommand(
          `window.solmateMap && window.solmateMap.setMarker(${selectedCoords.latitude}, ${selectedCoords.longitude}, ${selectedCoords.zoom || 16}); window.solmateMap && window.solmateMap.invalidateMapSize();`,
        ),
      );
    }, 150);

    return () => {
      clearTimeout(timer);
    };
  }, [
    mapReady,
    selectedCoords.latitude,
    selectedCoords.longitude,
    selectedCoords.zoom,
    visible,
  ]);

  const showFeedback = (message: string, tone: 'error' | 'info' = 'info') => {
    setFeedback({message, tone});
  };

  const clearFeedback = () => {
    setFeedback(null);
  };

  const syncMapMarker = (
    latitude: number,
    longitude: number,
    zoom = 16,
  ) => {
    setSelectedCoords({latitude, longitude, zoom});

    if (!mapReady || !webViewRef.current) {
      return;
    }

    webViewRef.current.injectJavaScript(
      injectMapCommand(
        `window.solmateMap && window.solmateMap.setMarker(${latitude}, ${longitude}, ${zoom});`,
      ),
    );
  };

  const invalidateMapSize = () => {
    if (!webViewRef.current) {
      return;
    }

    webViewRef.current.injectJavaScript(
      injectMapCommand(
        'window.solmateMap && window.solmateMap.invalidateMapSize();',
      ),
    );
  };

  const handleMapMessage = (event: WebViewMessageEvent) => {
    let payload: MapWebMessage | null = null;

    try {
      payload = JSON.parse(event.nativeEvent.data) as MapWebMessage;
    } catch {
      return;
    }

    if (!payload) {
      return;
    }

    if (payload.type === 'ready') {
      setMapReady(true);
      return;
    }

    if (
      payload.type === 'markerChanged' ||
      payload.type === 'currentLocation'
    ) {
      if (
        Number.isFinite(payload.latitude) &&
        Number.isFinite(payload.longitude)
      ) {
        setSelectedCoords(current => ({
          latitude: payload.latitude,
          longitude: payload.longitude,
          zoom: current.zoom || 16,
        }));
        clearFeedback();
      }

      if (payload.type === 'currentLocation') {
        setLocating(false);
      }

      return;
    }

    if (payload.type === 'currentLocationError') {
      setLocating(false);

      if (payload.code === 1 || payload.code === 'DENIED') {
        showFeedback('Location access denied. Please pin manually.', 'error');
        return;
      }

      if (payload.code === 'UNAVAILABLE') {
        showFeedback('Location is unavailable. Please pin manually.', 'error');
        return;
      }

      showFeedback('Location is unavailable. Please pin manually.', 'error');
    }
  };

  const handleSearch = async () => {
    const trimmedQuery = searchQuery.trim();

    if (!trimmedQuery) {
      showFeedback('Please enter an address or landmark.', 'error');
      return;
    }

    try {
      setSearching(true);
      clearFeedback();

      const results = await nominatimRequest<NominatimSearchResult[]>(
        'search',
        {
          q: trimmedQuery,
          limit: '1',
        },
      );

      const firstResult = Array.isArray(results) ? results[0] : null;
      const latitude = Number(firstResult?.lat);
      const longitude = Number(firstResult?.lon);

      if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        showFeedback(
          'No location found. Try a more specific address.',
          'error',
        );
        return;
      }

      syncMapMarker(latitude, longitude);
    } catch {
      showFeedback(
        'Unable to search location right now. Please try again or pin manually.',
        'error',
      );
    } finally {
      setSearching(false);
    }
  };

  const handleUseCurrentLocation = async () => {
    if (locating) {
      return;
    }

    if (!mapReady || !webViewRef.current) {
      showFeedback('Map is still loading. Please try again.', 'error');
      return;
    }

    if (Platform.OS === 'android') {
      try {
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
          {
            title: 'Allow location access',
            message: permissionMessage,
            buttonPositive: 'Allow',
            buttonNegative: 'Deny',
          },
        );

        if (granted !== PermissionsAndroid.RESULTS.GRANTED) {
          showFeedback('Location access denied. Please pin manually.', 'error');
          return;
        }
      } catch {
        showFeedback('Location is unavailable. Please pin manually.', 'error');
        return;
      }
    }

    setLocating(true);
    clearFeedback();
    webViewRef.current.injectJavaScript(
      injectMapCommand(
        'window.solmateMap && window.solmateMap.requestCurrentLocation();',
      ),
    );
  };

  const handleConfirm = async () => {
    const latitude = Number(selectedCoords.latitude);
    const longitude = Number(selectedCoords.longitude);

    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
      showFeedback('Please pin a location on the map before confirming.');
      return;
    }

    try {
      setConfirming(true);
      clearFeedback();

      const result = await nominatimRequest<NominatimReverseResult>(
        'reverse',
        {
          lat: String(latitude),
          lon: String(longitude),
        },
      );

      const resolvedAddress =
        typeof result?.display_name === 'string' &&
        result.display_name.trim().length > 0
          ? result.display_name.trim()
          : null;

      onConfirm({
        latitude,
        longitude,
        resolvedAddress,
        reverseGeocodeFailed: !resolvedAddress,
      });
    } catch {
      onConfirm({
        latitude,
        longitude,
        resolvedAddress: null,
        reverseGeocodeFailed: true,
      });
    } finally {
      setConfirming(false);
    }
  };

  return (
    <Modal
      animationType="slide"
      onRequestClose={onCancel}
      presentationStyle="fullScreen"
      transparent
      visible={visible}>
      <SafeAreaView style={styles.overlay}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.overlay}>
          <View style={[styles.sheet, {maxHeight: sheetMaxHeight}]}>
            <View style={styles.header}>
              <View style={styles.headerTextWrap}>
                <Text style={styles.title}>{title}</Text>
                <Text style={styles.subtitle}>{subtitle}</Text>
              </View>
              <Pressable
                accessibilityLabel="Close location picker"
                onPress={onCancel}
                style={({pressed}) => [
                  styles.closeButton,
                  pressed && styles.pressed,
                ]}>
                <Text style={styles.closeButtonText}>×</Text>
              </Pressable>
            </View>

            <View style={styles.searchRow}>
              <TextInput
                autoCapitalize="words"
                autoCorrect={false}
                onChangeText={setSearchQuery}
                onSubmitEditing={handleSearch}
                placeholder="Search address or landmark"
                placeholderTextColor={MUTED}
                returnKeyType="search"
                style={styles.searchInput}
                value={searchQuery}
              />
              <Pressable
                disabled={searching}
                onPress={handleSearch}
                style={({pressed}) => [
                  styles.searchButton,
                  searching && styles.buttonDisabled,
                  pressed && !searching ? styles.pressed : null,
                ]}>
                {searching ? (
                  <ActivityIndicator color={NAVY} size="small" />
                ) : (
                  <Text style={styles.searchButtonText}>Search</Text>
                )}
              </Pressable>
            </View>

            <ScrollView
              bounces={false}
              contentContainerStyle={styles.contentScrollContent}
              keyboardShouldPersistTaps="handled"
              showsVerticalScrollIndicator={false}
              style={styles.contentScroll}>
              {feedback ? (
                <View
                  style={[
                    styles.feedbackBanner,
                    feedback.tone === 'error'
                      ? styles.feedbackBannerError
                      : styles.feedbackBannerInfo,
                  ]}>
                  <Text
                    style={[
                      styles.feedbackText,
                      feedback.tone === 'error'
                        ? styles.feedbackTextError
                        : styles.feedbackTextInfo,
                    ]}>
                    {feedback.message}
                  </Text>
                </View>
              ) : null}

              <View style={[styles.mapFrame, {height: mapHeight}]}>
                <WebView
                  geolocationEnabled
                  javaScriptEnabled
                  key={webViewKey}
                  onLoadEnd={invalidateMapSize}
                  onMessage={handleMapMessage}
                  originWhitelist={['*']}
                  ref={webViewRef}
                  setSupportMultipleWindows={false}
                  showsHorizontalScrollIndicator={false}
                  showsVerticalScrollIndicator={false}
                  source={{
                    html: mapHtml,
                    baseUrl: 'https://solmate.local',
                  }}
                  startInLoadingState
                  style={styles.webView}
                />
              </View>
            </ScrollView>

            <View style={styles.actions}>
              <Pressable
                disabled={locating}
                onPress={handleUseCurrentLocation}
                style={({pressed}) => [
                  styles.softGoldButton,
                  locating && styles.buttonDisabled,
                  pressed && !locating ? styles.pressed : null,
                ]}>
                {locating ? (
                  <ActivityIndicator color={NAVY} size="small" />
                ) : (
                  <Text style={styles.softGoldButtonText}>
                    Use Current Location
                  </Text>
                )}
              </Pressable>

              <View style={styles.footerButtons}>
                <Pressable
                  onPress={onCancel}
                  style={({pressed}) => [
                    styles.secondaryButton,
                    pressed && styles.pressed,
                  ]}>
                  <Text style={styles.secondaryButtonText}>Cancel</Text>
                </Pressable>
                <Pressable
                  disabled={confirming}
                  onPress={handleConfirm}
                  style={({pressed}) => [
                    styles.primaryButton,
                    confirming && styles.buttonDisabled,
                    pressed && !confirming ? styles.pressed : null,
                  ]}>
                  {confirming ? (
                    <ActivityIndicator color={CARD} size="small" />
                  ) : (
                    <Text style={styles.primaryButtonText}>
                      Confirm Location
                    </Text>
                  )}
                </Pressable>
              </View>
            </View>
          </View>
        </KeyboardAvoidingView>
      </SafeAreaView>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.42)',
    justifyContent: 'flex-end',
  },
  sheet: {
    backgroundColor: CARD,
    borderTopLeftRadius: 28,
    borderTopRightRadius: 28,
    paddingHorizontal: 18,
    paddingTop: 18,
    paddingBottom: 16,
  },
  header: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  headerTextWrap: {
    flex: 1,
    paddingRight: 12,
  },
  title: {
    color: NAVY,
    fontSize: 22,
    fontWeight: '900',
    marginBottom: 4,
  },
  subtitle: {
    color: MUTED,
    fontSize: 13,
    lineHeight: 18,
  },
  closeButton: {
    alignItems: 'center',
    backgroundColor: '#f7f9fc',
    borderRadius: 18,
    height: 36,
    justifyContent: 'center',
    width: 36,
  },
  closeButtonText: {
    color: NAVY,
    fontSize: 26,
    lineHeight: 28,
    marginTop: -2,
  },
  feedbackBanner: {
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 12,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  feedbackBannerError: {
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
  },
  feedbackBannerInfo: {
    backgroundColor: '#EAF9FD',
    borderColor: '#bfdbfe',
  },
  feedbackText: {
    fontSize: 13,
    lineHeight: 18,
  },
  feedbackTextError: {
    color: '#b91c1c',
  },
  feedbackTextInfo: {
    color: '#1d4ed8',
  },
  searchRow: {
    alignItems: 'stretch',
    flexDirection: 'row',
    gap: 10,
    marginBottom: 12,
  },
  searchInput: {
    backgroundColor: '#f7f9fc',
    borderColor: '#DDE7EE',
    borderRadius: 16,
    borderWidth: 1,
    color: NAVY,
    flex: 1,
    fontSize: 15,
    minHeight: 52,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  searchButton: {
    alignItems: 'center',
    alignSelf: 'stretch',
    backgroundColor: '#fce7a8',
    borderRadius: 16,
    justifyContent: 'center',
    minWidth: 96,
    paddingHorizontal: 18,
  },
  searchButtonText: {
    color: NAVY,
    fontSize: 15,
    fontWeight: '800',
  },
  mapFrame: {
    backgroundColor: '#EAF9FD',
    borderColor: '#dbe4f0',
    borderRadius: 22,
    borderWidth: 1,
    overflow: 'hidden',
  },
  webView: {
    backgroundColor: '#EAF9FD',
    flex: 1,
  },
  contentScroll: {
    flexShrink: 1,
  },
  contentScrollContent: {
    paddingBottom: 14,
  },
  actions: {
    gap: 12,
  },
  softGoldButton: {
    alignItems: 'center',
    backgroundColor: '#fce7a8',
    borderRadius: 20,
    justifyContent: 'center',
    minHeight: 54,
    paddingHorizontal: 18,
    paddingVertical: 14,
  },
  softGoldButtonText: {
    color: NAVY,
    fontSize: 15,
    fontWeight: '800',
  },
  footerButtons: {
    flexDirection: 'row',
    gap: 10,
  },
  secondaryButton: {
    alignItems: 'center',
    backgroundColor: CARD,
    borderColor: '#dfe6f0',
    borderRadius: 20,
    borderWidth: 1,
    flex: 1,
    justifyContent: 'center',
    minHeight: 54,
    paddingHorizontal: 16,
  },
  secondaryButtonText: {
    color: NAVY,
    fontSize: 15,
    fontWeight: '800',
  },
  primaryButton: {
    alignItems: 'center',
    backgroundColor: GOLD,
    borderRadius: 20,
    flex: 1,
    justifyContent: 'center',
    minHeight: 54,
    paddingHorizontal: 16,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.22,
    shadowRadius: 10,
    elevation: 4,
  },
  primaryButtonText: {
    color: CARD,
    fontSize: 15,
    fontWeight: '900',
  },
  buttonDisabled: {
    opacity: 0.65,
  },
  pressed: {
    opacity: 0.85,
  },
});
