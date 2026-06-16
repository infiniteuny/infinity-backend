import { trackedRequest } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testUserCommunityGroups(ids) {
    trackedRequest('GET', `${BASE_URL}/v1/users/${ids.userId}/community-groups`, null, AUTH_HEADERS, 'user-community-groups');
}