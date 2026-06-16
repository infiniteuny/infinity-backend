import { trackedRequest } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testTokens() {
    trackedRequest('GET', `${BASE_URL}/v1/tokens`, null, AUTH_HEADERS, 'tokens');
}