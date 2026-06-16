import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testAchievements(ids) {
    let res, resourceId;
    const achFields = {
        team_id: ids.teamId,
        competition_instance_id: ids.competitionInstanceId,
        competition_scale_id: ids.competitionScaleId,
        competition_time_range_id: ids.competitionTimeRangeId,
        competition_output_id: ids.competitionOutputId,
        competition_rank_id: ids.competitionRankId,
        competition_branch: `k6branch${uid()}`,
        competition_start_date: '2025-01-01',
        competition_end_date: '2025-12-31',
        description: 'k6 achievement test',
        status: 'PENDING',
        image: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
    };

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/achievements`,
        achFields,
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'achievements'
    );
    check(res, { 'Achievements CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/achievements/${resourceId}`, null, AUTH_HEADERS, 'achievements');
        check(res, { 'Achievements READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/achievements/${resourceId}`,
            { ...achFields, _method: 'PUT', description: 'k6 updated' },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'achievements'
        );
        check(res, { 'Achievements UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/achievements/${resourceId}`, null, AUTH_HEADERS, 'achievements');
        check(res, { 'Achievements DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/achievements`, null, AUTH_HEADERS, 'achievements');
}
