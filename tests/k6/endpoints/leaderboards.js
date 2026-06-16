import { trackedRequest } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testLeaderboards() {
    trackedRequest('GET', `${BASE_URL}/v1/leaderboards/achievements`, null, AUTH_HEADERS, 'leaderboards');
}