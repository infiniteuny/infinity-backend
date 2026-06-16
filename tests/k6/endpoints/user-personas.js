import { check } from 'k6';
import { trackedRequest, extractId } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testUserPersonas(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/users/${ids.userId}/personas`,
        JSON.stringify({ persona_id: ids.personaId }),
        JSON_HEADERS, 'user-personas'
    );
    check(res, { 'UserPersonas CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/user-personas/${resourceId}`, null, AUTH_HEADERS, 'user-personas');
        check(res, { 'UserPersonas READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/user-personas/${resourceId}`,
            JSON.stringify({ persona_id: ids.personaId }),
            JSON_HEADERS, 'user-personas'
        );
        check(res, { 'UserPersonas UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/user-personas/${resourceId}`, null, AUTH_HEADERS, 'user-personas');
        check(res, { 'UserPersonas DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/users/${ids.userId}/personas`, null, AUTH_HEADERS, 'user-personas');
}