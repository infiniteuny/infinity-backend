import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, PDF_BIN, DOCX_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testFundApplications(ids) {
    let res, resourceId;
    const faFields = {
        team_id: ids.teamId,
        competition_instance_id: ids.competitionInstanceId,
        competition_scale_id: ids.competitionScaleId,
        competition_branch: `k6fabranch${uid()}`,
        competition_start_date: '2025-01-01',
        competition_end_date: '2025-12-31',
        status: 'PENDING',
        letter_of_acceptance: http.file(PDF_BIN, 'test-document.pdf', 'application/pdf'),
        proposal: http.file(DOCX_BIN, 'test-document.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    };

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/fund-applications`,
        faFields,
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'fund-applications'
    );
    check(res, { 'FundApplications CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/fund-applications/${resourceId}`, null, AUTH_HEADERS, 'fund-applications');
        check(res, { 'FundApplications READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/fund-applications/${resourceId}`,
            { ...faFields, _method: 'PUT' },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'fund-applications'
        );
        check(res, { 'FundApplications UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/fund-applications/${resourceId}`, null, AUTH_HEADERS, 'fund-applications');
        check(res, { 'FundApplications DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/fund-applications`, null, AUTH_HEADERS, 'fund-applications');
}
