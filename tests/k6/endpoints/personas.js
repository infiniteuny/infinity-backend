import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testPersonas() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/personas`,
        {
            name: `k6persona_crud${uid()}`,
            priority: '5',
            description: 'k6 crud test',
            logo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
        },
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'personas'
    );
    check(res, { 'Personas CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/personas/${resourceId}`, null, AUTH_HEADERS, 'personas');
        check(res, { 'Personas READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/personas/${resourceId}`,
            {
                _method: 'PUT',
                name: `k6persona_upd${uid()}`,
                priority: '5',
                description: 'k6 crud test updated',
                logo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'personas'
        );
        check(res, { 'Personas UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/personas/${resourceId}`, null, AUTH_HEADERS, 'personas');
        check(res, { 'Personas DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/personas`, null, AUTH_HEADERS, 'personas');
}
