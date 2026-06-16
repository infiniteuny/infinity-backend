import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testPersonas() {
    let res, resourceId;
    const personaData = { name: `k6persona_crud${uid()}`, priority: 5, description: 'k6 crud test' };
    const personaFd = createMultipartForm(personaData, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/personas`,
        personaFd.body,
        { ...AUTH_HEADERS, ...personaFd.headers, Accept: 'application/json' },
        'personas'
    );
    check(res, { 'Personas CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/personas/${resourceId}`, null, AUTH_HEADERS, 'personas');
        check(res, { 'Personas READ': (r) => r.status === 200 });

        const personaUpdFd = createMultipartForm({ ...personaData, name: `k6persona_upd${uid()}` }, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/personas/${resourceId}`,
            personaUpdFd.body,
            { ...AUTH_HEADERS, ...personaUpdFd.headers, Accept: 'application/json' },
            'personas'
        );
        check(res, { 'Personas UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/personas/${resourceId}`, null, AUTH_HEADERS, 'personas');
        check(res, { 'Personas DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/personas`, null, AUTH_HEADERS, 'personas');
}