import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCompetitionInstances(ids) {
    let res, resourceId;
    const ciData = {
        competition_id: ids.competitionId,
        name: `k6inst_crud${uid()}`,
        shortname: 'CI',
        description: 'k6',
        url: 'https://example.com',
        organizer: 'k6',
        organizer_type_id: ids.competitionOrganizerTypeId,
        start_date: '2025-01-01',
        end_date: '2025-12-31',
        location: 'k6 test location',
        logo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
    };

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-instances`,
        ciData,
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'competition-instances'
    );
    check(res, { 'CompInstances CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-instances/${resourceId}`, null, AUTH_HEADERS, 'competition-instances');
        check(res, { 'CompInstances READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-instances/${resourceId}`,
            { ...ciData, _method: 'PUT', name: `k6inst_upd${uid()}` },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'competition-instances'
        );
        check(res, { 'CompInstances UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-instances/${resourceId}`, null, AUTH_HEADERS, 'competition-instances');
        check(res, { 'CompInstances DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-instances`, null, AUTH_HEADERS, 'competition-instances');
}
