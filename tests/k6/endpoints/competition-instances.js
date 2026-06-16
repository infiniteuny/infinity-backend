import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCompetitionInstances(ids) {
    let res, resourceId;
    const ciData = {
        competition_id: ids.competitionId,
        name: `k6inst_crud${uid()}`,
        shortname: `CI${uid()}`,
        description: 'k6',
        url: 'https://example.com',
        organizer: 'k6',
        organizer_type_id: ids.competitionOrganizerTypeId,
        start_date: '2025-01-01',
        end_date: '2025-12-31',
        location: 'k6 test location',
    };
    const ciFd = createMultipartForm(ciData, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-instances`,
        ciFd.body,
        { ...AUTH_HEADERS, ...ciFd.headers, Accept: 'application/json' },
        'competition-instances'
    );
    check(res, { 'CompInstances CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-instances/${resourceId}`, null, AUTH_HEADERS, 'competition-instances');
        check(res, { 'CompInstances READ': (r) => r.status === 200 });

        const ciUpdFd = createMultipartForm({ ...ciData, name: `k6inst_upd${uid()}` }, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-instances/${resourceId}`,
            ciUpdFd.body,
            { ...AUTH_HEADERS, ...ciUpdFd.headers, Accept: 'application/json' },
            'competition-instances'
        );
        check(res, { 'CompInstances UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-instances/${resourceId}`, null, AUTH_HEADERS, 'competition-instances');
        check(res, { 'CompInstances DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-instances`, null, AUTH_HEADERS, 'competition-instances');
}