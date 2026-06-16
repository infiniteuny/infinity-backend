import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCoreTeams() {
    let res, resourceId;
    const year = 9000 + __VU;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/core-teams`,
        JSON.stringify({ year: year, is_active: false }),
        JSON_HEADERS, 'core-teams'
    );
    check(res, { 'CoreTeams CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/core-teams/${resourceId}`, null, AUTH_HEADERS, 'core-teams');
        check(res, { 'CoreTeams READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/core-teams/${resourceId}`,
            JSON.stringify({ year: year, is_active: false }),
            JSON_HEADERS, 'core-teams'
        );
        check(res, { 'CoreTeams UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/core-teams/${resourceId}`, null, AUTH_HEADERS, 'core-teams');
        check(res, { 'CoreTeams DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/core-teams`, null, AUTH_HEADERS, 'core-teams');
}