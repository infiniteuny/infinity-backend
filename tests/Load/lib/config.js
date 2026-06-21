export const BASE_URL = __ENV.K6_BASE_URL || 'https://api.infiniteuny.id/infinity';
export const BEARER_TOKEN = __ENV.K6_BEARER_TOKEN || 'REPLACE_WITH_YOUR_BEARER_TOKEN';

export const AUTH_HEADERS = {
    Authorization: `Bearer ${BEARER_TOKEN}`,
};

export const JSON_HEADERS = {
    ...AUTH_HEADERS,
    'Content-Type': 'application/json',
    Accept: 'application/json',
};