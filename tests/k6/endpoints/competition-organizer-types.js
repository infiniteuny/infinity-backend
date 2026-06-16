import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCompetitionOrganizerTypes() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-organizer-types`,
        JSON.stringify({ name: `k6cot_crud${uid()}`, weight: 1 }),
        JSON_HEADERS, 'competition-organizer-types'
    );
    check(res, { 'CompOrgTypes CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-organizer-types/${resourceId}`, null, AUTH_HEADERS, 'competition-organizer-types');
        check(res, { 'CompOrgTypes READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/competition-organizer-types/${resourceId}`,
            JSON.stringify({ name: `k6cot_upd${uid()}`, weight: 2 }),
            JSON_HEADERS, 'competition-organizer-types'
        );
        check(res, { 'CompOrgTypes UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-organizer-types/${resourceId}`, null, AUTH_HEADERS, 'competition-organizer-types');
        check(res, { 'CompOrgTypes DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-organizer-types`, null, AUTH_HEADERS, 'competition-organizer-types');
}