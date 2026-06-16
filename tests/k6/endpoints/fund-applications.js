import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testFundApplications(ids) {
    let res, resourceId;
    const faData = {
        team_id: ids.teamId,
        competition_instance_id: ids.competitionInstanceId,
        competition_scale_id: ids.competitionScaleId,
        competition_branch: `k6fabranch${uid()}`,
        competition_start_date: '2025-01-01',
        competition_end_date: '2025-12-31',
        status: 'PENDING',
    };
    const faFd = createMultipartForm(faData, {
        letter_of_acceptance: { filename: 'test-document.pdf', contentType: 'application/pdf' },
        proposal: { filename: 'test-document.pdf', contentType: 'application/pdf' },
    });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/fund-applications`,
        faFd.body,
        { ...AUTH_HEADERS, ...faFd.headers, Accept: 'application/json' },
        'fund-applications'
    );
    check(res, { 'FundApplications CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/fund-applications/${resourceId}`, null, AUTH_HEADERS, 'fund-applications');
        check(res, { 'FundApplications READ': (r) => r.status === 200 });

        const faUpdFd = createMultipartForm({ ...faData, status: 'ACCEPTED' }, {
            letter_of_acceptance: { filename: 'test-document.pdf', contentType: 'application/pdf' },
            proposal: { filename: 'test-document.pdf', contentType: 'application/pdf' },
        }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/fund-applications/${resourceId}`,
            faUpdFd.body,
            { ...AUTH_HEADERS, ...faUpdFd.headers, Accept: 'application/json' },
            'fund-applications'
        );
        check(res, { 'FundApplications UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/fund-applications/${resourceId}`, null, AUTH_HEADERS, 'fund-applications');
        check(res, { 'FundApplications DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/fund-applications`, null, AUTH_HEADERS, 'fund-applications');
}