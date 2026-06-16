import http from 'k6/http';
import { group, sleep } from 'k6';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from './lib/config.js';
import { trackedRequest, extractId, uid, createMultipartForm } from './lib/helpers.js';

// Endpoint modules
import { testDegrees } from './endpoints/degrees.js';
import { testFaculties } from './endpoints/faculties.js';
import { testMajors } from './endpoints/majors.js';
import { testCompetitionOrganizerTypes } from './endpoints/competition-organizer-types.js';
import { testCompetitionOutputs } from './endpoints/competition-outputs.js';
import { testCompetitionRanks } from './endpoints/competition-ranks.js';
import { testCompetitionScales } from './endpoints/competition-scales.js';
import { testCompetitionTeamTypes } from './endpoints/competition-team-types.js';
import { testCompetitionTimeRanges } from './endpoints/competition-time-ranges.js';
import { testCompetitions } from './endpoints/competitions.js';
import { testCompetitionInstances } from './endpoints/competition-instances.js';
import { testCommunityGroupAdmins } from './endpoints/community-group-admins.js';
import { testCommunityGroupAdminMembers } from './endpoints/community-group-admin-members.js';
import { testCommunityGroups } from './endpoints/community-groups.js';
import { testCommunityGroupMembers } from './endpoints/community-group-members.js';
import { testConfigs } from './endpoints/configs.js';
import { testCoreTeamDivisions } from './endpoints/core-team-divisions.js';
import { testCoreTeams } from './endpoints/core-teams.js';
import { testCoreTeamMembers } from './endpoints/core-team-members.js';
import { testGroups } from './endpoints/groups.js';
import { testGroupPermissions } from './endpoints/group-permissions.js';
import { testLeaderboards } from './endpoints/leaderboards.js';
import { testPermissions } from './endpoints/permissions.js';
import { testPersonas } from './endpoints/personas.js';
import { testProjectGalleries } from './endpoints/project-galleries.js';
import { testTeams } from './endpoints/teams.js';
import { testTeamMembers } from './endpoints/team-members.js';
import { testTestimonials } from './endpoints/testimonials.js';
import { testTokens } from './endpoints/tokens.js';
import { testUserCommunityGroups } from './endpoints/user-community-groups.js';
import { testUserGroups } from './endpoints/user-groups.js';
import { testUserPermissions } from './endpoints/user-permissions.js';
import { testUserPersonas } from './endpoints/user-personas.js';
import { testUsers } from './endpoints/users.js';
import { testAchievements } from './endpoints/achievements.js';
import { testFundApplications } from './endpoints/fund-applications.js';

// ============================================================================
// Breakpoint Test Options
// ============================================================================
export const options = {
    scenarios: {
        breakpoint: {
            executor: 'ramping-vus',
            startVUs: 1,
            stages: [
                { duration: '1m', target: 10 },
                { duration: '2m', target: 25 },
                { duration: '2m', target: 50 },
                { duration: '3m', target: 100 },
                { duration: '2m', target: 200 },
                { duration: '3m', target: 0 },
            ],
            gracefulRampDown: '30s',
        },
    },
    thresholds: {
        http_req_failed: ['rate<0.15'],
        http_req_duration: ['p(95)<10000'],
        api_error_rate: ['rate<0.2'],
    },
    summaryTrendStats: ['avg', 'min', 'med', 'max', 'p(90)', 'p(95)', 'p(99)'],
};

// ============================================================================
// Setup: Create prerequisite resources
// ============================================================================
export function setup() {
    const ids = {};

    group('Setup - Independent Resources', () => {
        // Degrees
        let res = trackedRequest(
            'POST', `${BASE_URL}/v1/degrees`,
            JSON.stringify({ name: `k6_setup_deg${uid()}`, code: `K6DEG${uid()}` }),
            JSON_HEADERS, 'setup'
        );
        ids.degreeId = extractId(res) || 'test-degree-id';

        // Faculties
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/faculties`,
            JSON.stringify({ name: `k6_setup_fac${uid()}`, code: `K6FAC${uid()}`, shortname: `KF${uid()}`, description: 'k6 setup' }),
            JSON_HEADERS, 'setup'
        );
        ids.facultyId = extractId(res) || 'test-faculty-id';

        // Majors
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/majors`,
            JSON.stringify({ degree_id: ids.degreeId, faculty_id: ids.facultyId, code: `K6C${uid()}`, name: `k6_setup_major${uid()}` }),
            JSON_HEADERS, 'setup'
        );
        ids.majorId = extractId(res) || 'test-major-id';

        // Competition Organizer Types
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-organizer-types`,
            JSON.stringify({ name: `k6_setup_orgtype${uid()}`, weight: 1 }),
            JSON_HEADERS, 'setup'
        );
        ids.competitionOrganizerTypeId = extractId(res) || 'test-cot-id';

        // Competition Outputs
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-outputs`,
            JSON.stringify({ name: `k6_setup_output${uid()}`, weight: 1 }),
            JSON_HEADERS, 'setup'
        );
        ids.competitionOutputId = extractId(res) || 'test-co-id';

        // Competition Ranks
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-ranks`,
            JSON.stringify({ name: `k6_setup_rank${uid()}`, weight: 1 }),
            JSON_HEADERS, 'setup'
        );
        ids.competitionRankId = extractId(res) || 'test-cr-id';

        // Competition Scales
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-scales`,
            JSON.stringify({ name: `k6_setup_scale${uid()}`, weight: 1 }),
            JSON_HEADERS, 'setup'
        );
        ids.competitionScaleId = extractId(res) || 'test-cs-id';

        // Competition Team Types
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-team-types`,
            JSON.stringify({ name: `k6_setup_teamtype${uid()}`, weight: 1 }),
            JSON_HEADERS, 'setup'
        );
        ids.competitionTeamTypeId = extractId(res) || 'test-ctt-id';

        // Competition Time Ranges
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-time-ranges`,
            JSON.stringify({ name: `k6_setup_timerange${uid()}`, weight: 1 }),
            JSON_HEADERS, 'setup'
        );
        ids.competitionTimeRangeId = extractId(res) || 'test-ctr-id';

        // Competitions
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competitions`,
            JSON.stringify({ name: `k6_setup_comp${uid()}`, shortname: `KC`, description: 'k6 setup' }),
            JSON_HEADERS, 'setup'
        );
        ids.competitionId = extractId(res) || 'test-comp-id';

        // Competition Instances (multipart)
        const ciData = {
            competition_id: ids.competitionId,
            name: `k6_setup_inst${uid()}`,
            shortname: `KI`,
            description: 'k6 setup',
            url: 'https://example.com',
            organizer: 'k6',
            organizer_type_id: ids.competitionOrganizerTypeId,
            start_date: '2025-01-01',
            end_date: '2025-12-31',
            location: 'k6 setup location',
        };
        const ciFd = createMultipartForm(ciData, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/competition-instances`,
            ciFd.body,
            { ...AUTH_HEADERS, ...ciFd.headers, Accept: 'application/json' },
            'setup'
        );
        ids.competitionInstanceId = extractId(res) || 'test-ci-id';

        // Core Team Divisions
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/core-team-divisions`,
            JSON.stringify({ name: `k6_setup_div${uid()}`, priority: 1 }),
            JSON_HEADERS, 'setup'
        );
        ids.coreTeamDivisionId = extractId(res) || 'test-ctd-id';

        // Core Teams
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/core-teams`,
            JSON.stringify({ year: 9000, is_active: true }),
            JSON_HEADERS, 'setup'
        );
        ids.coreTeamId = extractId(res) || 'test-ct-id';

        // Groups
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/groups`,
            JSON.stringify({ name: `k6_setup_group${uid()}`, guard_name: 'api' }),
            JSON_HEADERS, 'setup'
        );
        ids.groupId = extractId(res) || 'test-group-id';

        // Permissions
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/permissions`,
            JSON.stringify({ name: `k6_setup_perm${uid()}`, guard_name: 'api' }),
            JSON_HEADERS, 'setup'
        );
        ids.permissionId = extractId(res) || 'test-perm-id';

        // Personas (multipart)
        const personaData = { name: `k6_setup_persona${uid()}`, priority: 1, description: 'k6 setup persona' };
        const personaFd = createMultipartForm(personaData, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/personas`,
            personaFd.body,
            { ...AUTH_HEADERS, ...personaFd.headers, Accept: 'application/json' },
            'setup'
        );
        ids.personaId = extractId(res) || 'test-persona-id';

        // Users
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/users`,
            JSON.stringify({ name: `k6_setup_user${uid()}`, username: `k6su${uid()}`, email_address: `k6su${uid()}@test.com`, phone_number: '08123456789', student_id: `11111${uid().replace(/_/g, '')}`, major_id: ids.majorId, links: {}, is_member: true, is_extraordinary: false }),
            JSON_HEADERS, 'setup'
        );
        ids.userId = extractId(res) || 'test-user-id';

        // Community Group Admins
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/community-group-admins`,
            JSON.stringify({ year: 9000, is_active: true }),
            JSON_HEADERS, 'setup'
        );
        ids.communityGroupAdminId = extractId(res) || 'test-cga-id';

        // Community Groups (multipart)
        const cgData = { name: `k6_setup_cg${uid()}`, priority: 1, description: 'k6 setup', is_active: '1' };
        const cgFd = createMultipartForm(cgData, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/community-groups`,
            cgFd.body,
            { ...AUTH_HEADERS, ...cgFd.headers, Accept: 'application/json' },
            'setup'
        );
        ids.communityGroupId = extractId(res) || 'test-cg-id';

        // Configs key
        ids.configKey = `k6_setup_cfg${uid()}`;

        // Teams
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/teams`,
            JSON.stringify({ leader_id: ids.userId, team_type_id: ids.competitionTeamTypeId, name: `k6_setup_team${uid()}`, is_personal: false }),
            JSON_HEADERS, 'setup'
        );
        ids.teamId = extractId(res) || 'test-team-id';

        // Testimonials (multipart)
        const testData = { name: `k6_setup_testimonial${uid()}`, position: 'k6 setup tester', content: 'k6 setup content' };
        const testFd = createMultipartForm(testData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/testimonials`,
            testFd.body,
            { ...AUTH_HEADERS, ...testFd.headers, Accept: 'application/json' },
            'setup'
        );
        ids.testimonialId = extractId(res) || 'test-testimonial-id';

        // Project Galleries (multipart)
        const pgData = { title: `k6_setup_pg${uid()}`, description: 'k6 setup', url: 'https://example.com' };
        const pgFd = createMultipartForm(pgData, { image: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/project-galleries`,
            pgFd.body,
            { ...AUTH_HEADERS, ...pgFd.headers, Accept: 'application/json' },
            'setup'
        );
        ids.projectGalleryId = extractId(res) || 'test-pg-id';
    });

    return ids;
}

// ============================================================================
// Teardown: Cleanup
// ============================================================================
export function teardown(ids) {
    if (!ids) return;

    group('Teardown - Cleanup', () => {
        const deletions = [
            ids.testimonialId ? `/v1/testimonials/${ids.testimonialId}` : null,
            ids.projectGalleryId ? `/v1/project-galleries/${ids.projectGalleryId}` : null,
            ids.teamId ? `/v1/teams/${ids.teamId}` : null,
            ids.personaId ? `/v1/personas/${ids.personaId}` : null,
            ids.communityGroupId ? `/v1/community-groups/${ids.communityGroupId}` : null,
            ids.communityGroupAdminId ? `/v1/community-group-admins/${ids.communityGroupAdminId}` : null,
            ids.userId ? `/v1/users/${ids.userId}` : null,
            ids.groupId ? `/v1/groups/${ids.groupId}` : null,
            ids.permissionId ? `/v1/permissions/${ids.permissionId}` : null,
            ids.coreTeamId ? `/v1/core-teams/${ids.coreTeamId}` : null,
            ids.coreTeamDivisionId ? `/v1/core-team-divisions/${ids.coreTeamDivisionId}` : null,
            ids.competitionInstanceId ? `/v1/competition-instances/${ids.competitionInstanceId}` : null,
            ids.competitionId ? `/v1/competitions/${ids.competitionId}` : null,
            ids.competitionTeamTypeId ? `/v1/competition-team-types/${ids.competitionTeamTypeId}` : null,
            ids.competitionTimeRangeId ? `/v1/competition-time-ranges/${ids.competitionTimeRangeId}` : null,
            ids.competitionScaleId ? `/v1/competition-scales/${ids.competitionScaleId}` : null,
            ids.competitionRankId ? `/v1/competition-ranks/${ids.competitionRankId}` : null,
            ids.competitionOutputId ? `/v1/competition-outputs/${ids.competitionOutputId}` : null,
            ids.competitionOrganizerTypeId ? `/v1/competition-organizer-types/${ids.competitionOrganizerTypeId}` : null,
            ids.majorId ? `/v1/majors/${ids.majorId}` : null,
            ids.facultyId ? `/v1/faculties/${ids.facultyId}` : null,
            ids.degreeId ? `/v1/degrees/${ids.degreeId}` : null,
        ];

        for (const path of deletions) {
            if (path) {
                trackedRequest('DELETE', `${BASE_URL}${path}`, null, AUTH_HEADERS, 'teardown');
            }
        }
    });
}

// ============================================================================
// Default: Run all endpoint tests
// ============================================================================
export default function (setupIds) {
    const ids = setupIds || {};

    // Independent resources
    group('Degrees CRUD', () => testDegrees());
    group('Faculties CRUD', () => testFaculties());
    group('Majors CRUD', () => testMajors(ids));
    group('Comp Organizer Types CRUD', () => testCompetitionOrganizerTypes());
    group('Comp Outputs CRUD', () => testCompetitionOutputs());
    group('Comp Ranks CRUD', () => testCompetitionRanks());
    group('Comp Scales CRUD', () => testCompetitionScales());
    group('Comp Team Types CRUD', () => testCompetitionTeamTypes());
    group('Comp Time Ranges CRUD', () => testCompetitionTimeRanges());
    group('Competitions CRUD', () => testCompetitions());
    group('Comp Instances CRUD', () => testCompetitionInstances(ids));
    group('Comm Group Admins CRUD', () => testCommunityGroupAdmins());
    group('Comm Group Admin Members CRUD', () => testCommunityGroupAdminMembers(ids));
    group('Comm Groups CRUD', () => testCommunityGroups());
    group('Comm Group Members CRUD', () => testCommunityGroupMembers(ids));
    group('Configs CRUD', () => testConfigs());
    group('Core Team Divisions CRUD', () => testCoreTeamDivisions());
    group('Core Teams CRUD', () => testCoreTeams());
    group('Core Team Members CRUD', () => testCoreTeamMembers(ids));
    group('Groups CRUD', () => testGroups());
    group('Group Permissions CRUD', () => testGroupPermissions(ids));
    group('Leaderboards READ', () => testLeaderboards());
    group('Permissions CRUD', () => testPermissions());
    group('Personas CRUD', () => testPersonas());
    group('Project Galleries CRUD', () => testProjectGalleries());
    group('Teams CRUD', () => testTeams(ids));
    group('Team Members CRUD', () => testTeamMembers(ids));
    group('Testimonials CRUD', () => testTestimonials());
    group('Tokens READ', () => testTokens());
    group('User Community Groups READ', () => testUserCommunityGroups(ids));
    group('User Groups CRUD', () => testUserGroups(ids));
    group('User Permissions CRUD', () => testUserPermissions(ids));
    group('User Personas CRUD', () => testUserPersonas(ids));
    group('Users CRUD', () => testUsers(ids));
    group('Achievements CRUD', () => testAchievements(ids));
    group('Fund Applications CRUD', () => testFundApplications(ids));

    sleep(0.5);
};

// ============================================================================
// handleSummary: Write JSON results to file, standard k6 summary to console
// ============================================================================
export function handleSummary(data) {
    const logsDir = __ENV.K6_LOGS_DIR || 'tests/k6/logs';
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');

    const metrics = data.metrics;
    const httpReqDuration = metrics.http_req_duration?.values;
    const httpReqFailed = metrics.http_req_failed?.values;
    const apiErrorRate = metrics.api_error_rate?.values;
    const iterations = metrics.iterations?.values;

    const fmt = (v) => (v != null ? v.toFixed(2) : 'N/A');
    const pct = (val, key) => {
        if (!val) return 'N/A';
        const m = val[key];
        return m != null ? (m * 100).toFixed(2) + '%' : 'N/A';
    };

    const lines = [
        '\n=== k6 Breakpoint Test Summary ===\n',
        `Duration:        ${fmt(data.state?.testRunDurationMs / 1000)}s`,
        `Iterations:      ${iterations?.count ?? 'N/A'}`,
        `VUs (max):       ${data.root_group?.groups?.length ?? 'N/A'}`,
        '',
        '--- HTTP Request Duration (ms) ---',
        `  avg:   ${fmt(httpReqDuration?.avg)}`,
        `  min:   ${fmt(httpReqDuration?.min)}`,
        `  med:   ${fmt(httpReqDuration?.med)}`,
        `  max:   ${fmt(httpReqDuration?.max)}`,
        `  p(90): ${fmt(httpReqDuration?.['p(90)'])}`,
        `  p(95): ${fmt(httpReqDuration?.['p(95)'])}`,
        `  p(99): ${fmt(httpReqDuration?.['p(99)'])}`,
        '',
        '--- Error Rates ---',
        `  http_req_failed:  ${pct(httpReqFailed, 'rate')}`,
        `  api_error_rate:   ${pct(apiErrorRate, 'rate')}`,
        '',
        '--- Per-Group Summary ---',
    ];

    const groupNames = Object.keys(data.metrics).filter(k => k.endsWith('_response_time')).map(k => k.replace('_response_time', ''));
    for (const gn of groupNames) {
        const rt = metrics[`${gn}_response_time`]?.values;
        const er = metrics[`${gn}_error_rate`]?.values;
        const tp = metrics[`${gn}_throughput`]?.values;
        if (!rt) continue;
        lines.push(`  ${gn}: avg=${fmt(rt.avg)}ms  p(95)=${fmt(rt['p(95)'])}ms  errors=${pct(er, 'rate')}  reqs=${tp?.count ?? 0}`);
    }
    lines.push('\n=== End Summary ===\n');

    return {
        stdout: lines.join('\n'),
        [`${logsDir}/results-${timestamp}.json`]: JSON.stringify(data, null, 2),
    };
}