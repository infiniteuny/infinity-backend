import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testMajors(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/majors`,
        JSON.stringify({ degree_id: ids.degreeId, faculty_id: ids.facultyId, code: `MC${uid()}`, name: `k6major_crud${uid()}` }),
        JSON_HEADERS, 'majors'
    );
    check(res, { 'Majors CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/majors/${resourceId}`, null, AUTH_HEADERS, 'majors');
        check(res, { 'Majors READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/majors/${resourceId}`,
            JSON.stringify({ degree_id: ids.degreeId, faculty_id: ids.facultyId, code: `MU${uid()}`, name: `k6major_upd${uid()}` }),
            JSON_HEADERS, 'majors'
        );
        check(res, { 'Majors UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/majors/${resourceId}`, null, AUTH_HEADERS, 'majors');
        check(res, { 'Majors DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/majors`, null, AUTH_HEADERS, 'majors');
}