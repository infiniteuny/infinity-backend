# Load Testing Blueprint

A production-grade methodology for stress-testing the Infinity backend infrastructure, identifying breaking points, and hardening the system before failures hit production.

---

## Table of Contents

1. [Phase 1: Architectural Triage & Profiling](#phase-1-architectural-triage--profiling)
2. [Phase 2: Defining the Breakpoints](#phase-2-defining-the-breakpoints)
3. [Phase 3: Executing the Mixed-Load Profile](#phase-3-executing-the-mixed-load-profile)
4. [Phase 4: The Autopsy and Recovery](#phase-4-the-autopsy-and-recovery)
5. [Toolchain & Observability](#toolchain--observability)
6. [Appendix: Endpoint Classification Matrix](#appendix-endpoint-classification-matrix)

---

## Phase 1: Architectural Triage & Profiling

Do not test everything. Map out risk vectors based on real-world probability and financial impact, not chronological logic.

### 1.1 The Volume Grinders (Reads)

The highest traffic tier, often representing 80%+ of total requests.

- Identify the top 2-3 read endpoints.
- Goal: prove caching layers, indexes, and connection pools will not exhaust under relentless querying.
- Key metrics: cache hit ratio, query execution time, connection pool utilization.

### 1.2 The Financial Bottlenecks (Creates)

Where downtime equals lost revenue.

- Identify the core transaction endpoints (e.g., checkout, order creation, payment processing).
- Actively hunt for: database locks, third-party API timeouts, payment gateway bottlenecks.
- Key metrics: transaction throughput (TPS), lock wait times, external API latency.

### 1.3 The Concurrency Traps (Updates)

Endpoints most likely to suffer from race conditions.

- Identify endpoints handling concurrent state mutations (e.g., order status updates, inventory decrements).
- Test database isolation levels and deadlock resilience.
- Key metrics: deadlock count, row lock contention, optimistic lock failures.

### 1.4 The Noise (Deletes & Low-Impact)

- Strip these out of the test entirely.
- Do not waste compute and analysis time on edge cases.

---

## Phase 2: Defining the Breakpoints

A system fails long before it goes offline. Set hard, objective thresholds for degradation **before** running a single script.

### 2.1 Response Time

Average API response time for each request. The point where the system is functionally dead to the user.

| Metric            | Threshold | Severity       |
| ----------------- | --------- | -------------- |
| P50 response time | > 1s      | Warning        |
| P95 response time | > 3s      | Critical       |
| P99 response time | > 10s     | System failure |

### 2.2 Throughput

Number of requests that can be processed per second. The maximum sustainable request rate before the system begins rejecting or queuing requests.

| Metric                  | Threshold                   | Severity         |
| ----------------------- | --------------------------- | ---------------- |
| Requests/second drop    | > 20% from baseline         | Warning          |
| Requests/second drop    | > 50% from baseline         | Critical         |
| Requests/second plateau | No growth despite added VUs | Saturation point |

### 2.3 Error Rate

Percentage of failed requests. When the system starts silently dropping users.

| Metric                         | Threshold | Severity |
| ------------------------------ | --------- | -------- |
| HTTP 5xx rate                  | > 1%      | Warning  |
| HTTP 5xx rate                  | > 5%      | Critical |
| Dropped connections / timeouts | > 0.5%    | Critical |

### 2.4 Virtual Users

Maximum number of virtual users the system can handle before degradation occurs. The absolute concurrency ceiling of the system.

| Metric     | Threshold                  | Severity             |
| ---------- | -------------------------- | -------------------- |
| Active VUs | Degradation first observed | Baseline capacity    |
| Active VUs | Error rate exceeds 1%      | Max safe capacity    |
| Active VUs | System unresponsive        | Absolute break point |

---

## Phase 3: Executing the Mixed-Load Profile

Production traffic is a blend. Hitting the system with 100% "Create" traffic is a fantasy scenario. The test must mirror reality.

### 3.1 The Mix

Configure the load testing script to execute a proportional simulation using **k6 multi-scenario architecture**:

```
Reads:    80%  (GET endpoints - listings, details, search)
Creates:  15%  (POST endpoints - orders, registrations, payments)
Updates:   5%  (PUT/PATCH endpoints - status changes, profile updates)
```

**Implementation:** Each endpoint type has its own scenario with `ramping-vus` executor for direct VU control and gradual load increase while maintaining traffic distribution.

### 3.2 Multi-Scenario Architecture

Instead of random traffic distribution, use **dedicated scenarios** for each endpoint group with gradual VU ramp-up:

```javascript
scenarios: {
    reads_users: {
        executor: 'ramping-vus',
        startVUs: 0,
        stages: [
            { duration: '5m', target: 10 },
            { duration: '5m', target: 20 },
            { duration: '5m', target: 30 },
            { duration: '5m', target: 40 },
        ],
        gracefulRampDown: '30s',
        exec: 'readUsers',
    },
    creates_fund_applications: {
        executor: 'ramping-vus',
        startVUs: 0,
        stages: [
            { duration: '5m', target: 3 },
            { duration: '5m', target: 6 },
            { duration: '5m', target: 9 },
            { duration: '5m', target: 12 },
        ],
        gracefulRampDown: '30s',
        exec: 'createFundApplications',
    },
    updates_fund_applications: {
        executor: 'ramping-vus',
        startVUs: 0,
        stages: [
            { duration: '5m', target: 1 },
            { duration: '5m', target: 3 },
            { duration: '5m', target: 4 },
            { duration: '5m', target: 6 },
        ],
        gracefulRampDown: '30s',
        exec: 'updateFundApplications',
    },
    // ... more scenarios
}
```

**Benefits:**

- **Direct VU control** - VU count is explicitly controlled, not dependent on response time
- **Traffic mix preserved** - Maintains 80/15/5 distribution at all load levels
- **Gradual ramp-up** - Load increases smoothly to find breaking point
- **Independent VU pools** - Each scenario has its own VU allocation
- **Separate metrics** - Per-scenario response times and error rates
- **Automatic stop** - Test aborts when thresholds are breached

### 3.3 Traffic Distribution

| Category    | Scenarios                                                                      | Max VUs | Percentage |
| ----------- | ------------------------------------------------------------------------------ | ------- | ---------- |
| **Reads**   | users, user-permissions, competitions, configs, community-groups, testimonials | 160 VUs | 80%        |
| **Creates** | fund-applications, achievements, teams                                         | 30 VUs  | 15%        |
| **Updates** | fund-applications, achievements                                                | 10 VUs  | 5%         |
| **Total**   | 11 scenarios                                                                   | 200 VUs | 100%       |

### 3.4 The Baseline

- **Load:** 50 concurrent Virtual Users (VUs)
- **Duration:** 5 minutes
- **Pass criteria:** P95 < 500ms, error rate < 0.1%, zero 5xx responses

> If there is instability here, **stop**. You have fundamental code flaws, not scaling issues.

### 3.5 The Smooth Ramp-up

All scenarios ramp up together over 20 minutes in four gradual stages:

| Stage   | Duration | Target VUs      | Purpose                                |
| ------- | -------- | --------------- | -------------------------------------- |
| Stage 1 | 5 min    | 25% of max VUs  | Initial warm-up                        |
| Stage 2 | 5 min    | 50% of max VUs  | Baseline verification                  |
| Stage 3 | 5 min    | 75% of max VUs  | Load increase                          |
| Stage 4 | 5 min    | 100% of max VUs | Full load and breaking point detection |

**Ramp-up by scenario:**

| Scenario                  | Start VUs | Stage 1 (25%) | Stage 2 (50%) | Stage 3 (75%) | Stage 4 (100%) | Max VUs |
| ------------------------- | --------- | ------------- | ------------- | ------------- | -------------- | ------- |
| reads_users               | 0         | 12            | 25            | 37            | 50             | 50      |
| reads_user_permissions    | 0         | 10            | 20            | 30            | 40             | 40      |
| reads_competitions        | 0         | 7             | 15            | 22            | 30             | 30      |
| reads_configs             | 0         | 5             | 10            | 15            | 20             | 20      |
| reads_community_groups    | 0         | 2             | 5             | 7             | 10             | 10      |
| reads_testimonials        | 0         | 2             | 5             | 7             | 10             | 10      |
| creates_fund_applications | 0         | 3             | 6             | 9             | 12             | 12      |
| creates_achievements      | 0         | 2             | 5             | 7             | 10             | 10      |
| creates_teams             | 0         | 2             | 4             | 6             | 8              | 8       |
| updates_fund_applications | 0         | 1             | 3             | 4             | 6              | 6       |
| updates_achievements      | 0         | 1             | 2             | 3             | 4              | 4       |

This smooth ramp-up exposes the **exact load level** where degradation begins while maintaining realistic traffic distribution.

### 3.6 The Break

- Load increases gradually using `ramping-vus` executor with direct VU control
- Test automatically stops when thresholds are breached (`abortOnFail: true`)
- Thresholds that trigger abort:
    - P95 response time > 3s
    - P99 response time > 10s
    - Error rate > 1%
- Record the exact VU count, endpoint, and timestamp of failure

---

## Phase 4: The Autopsy and Recovery

The test is useless if you do not analyze the corpse. How does the architecture behave immediately _after_ the break?

### 4.1 The Self-Healing Check

| Check             | Expected Behavior                                | Failure Indicator                      |
| ----------------- | ------------------------------------------------ | -------------------------------------- |
| Auto-scalers      | New pods spin up within target window            | Pods fail to start or scale too slowly |
| Database recovery | Transaction backlog clears gracefully            | Backlog grows, deadlocks persist       |
| Error rate        | Drops to < 0.1% within 2 minutes of load removal | Cascading failure loop                 |
| Memory            | Returns to baseline after GC                     | Memory leak, requires manual restart   |
| Queue workers     | Drain pending jobs without intervention          | Stuck jobs, worker crashes             |

### 4.2 The Bottleneck Isolation

Pinpoint the exact failure point. Common culprits:

- **Unindexed queries** scanning millions of rows.
- **Synchronous heavy lifting** consuming App CPU (image processing, report generation).
- **Downstream dependency choke** (payment gateway, email service, third-party API).
- **Connection pool exhaustion** from slow queries holding connections too long.
- **Cache stampede** when a popular cache key expires under load.

### 4.3 The Resolution

Never blindly provision larger cloud instances to mask bad code.

| Problem                | Resolution                                               |
| ---------------------- | -------------------------------------------------------- |
| Slow queries           | Add/optimize indexes, rewrite N+1 queries                |
| CPU-bound sync work    | Offload to queue workers (Laravel Queues)                |
| Heavy write contention | Implement message queue to decouple writes               |
| Read overload          | Implement aggressive caching (Redis), read replicas      |
| Connection exhaustion  | Tune pool size, implement connection pooling (PgBouncer) |
| Third-party timeouts   | Implement circuit breakers, async callbacks              |

---

## Toolchain & Observability

### Load Testing Tool: k6

**Selected Tool:** k6 (Grafana k6)

**Why k6:**

- Developer-friendly JavaScript scripting (runs on Go runtime for performance)
- Native Grafana and Prometheus integration for real-time metrics
- Built-in cloud scaling capabilities for distributed load generation
- Excellent CI/CD integration for automated performance regression testing
- Perfect fit for mixed-load profiles with proportional traffic simulation

**Key Features for This Test Plan:**

- Multi-scenario architecture with `ramping-vus` executors for direct VU control
- 11 dedicated scenarios (6 reads, 3 creates, 2 updates) with independent VU pools
- Execute the 80/15/5 read/create/update mix with deterministic distribution
- Smooth VU ramp-up over 20 minutes to find breaking point
- Automatic test abort when thresholds are breached (abortOnFail)
- Per-scenario metrics for response times, error rates, and throughput
- Real-time threshold monitoring (response time, error rate, throughput)
- Detailed reports with latency percentiles and breakdown by scenario

### Observability Stack Requirements

During load tests, the following must be monitored in real time:

#### Application Layer

- APM traces (e.g., Laravel Telescope, New Relic, Datadog APM)
- Request rate, error rate, latency percentiles (P50/P95/P99)
- Queue depth and worker utilization

#### Database Layer

- Slow query log (enable during test window)
- Active connections and lock wait times
- Replication lag (if read replicas are in use)
- Deadlock detection logs

#### Infrastructure Layer

- CPU, memory, disk I/O per node
- Network throughput and connection counts
- Auto-scaler events and pod lifecycle

#### External Dependencies

- Third-party API response times and error rates
- Payment gateway latency and timeout counts
- Cache hit/miss ratios and eviction rates

---

## Appendix: Endpoint Classification Matrix

Only the high-impact endpoints are included. Per Phase 1, low-traffic admin/reference CRUD and delete operations are excluded entirely.

All scenarios use `ramping-vus` executor with smooth VU ramp-up over 20 minutes (4 stages of 5 minutes each, incrementing by 25% of target VUs).

### A. Volume Grinders (Reads) - 80% of test traffic (6 scenarios, 160 VUs max)

Top read endpoints hit by the frontend, landing page, and membership checker.

| Scenario                 | Endpoint                                 | Max VUs | Percentage | Notes                                                                       |
| ------------------------ | ---------------------------------------- | ------- | ---------- | --------------------------------------------------------------------------- |
| `reads_users`            | `/v1/users?includes=major,major.faculty` | 50      | 25%        | Hit on every authenticated page load; eager loads major + faculty relations |
| `reads_user_permissions` | `/v1/user-permissions`                   | 40      | 20%        | Hit on every authenticated page load for navigation/menu filtering          |
| `reads_competitions`     | `/v1/competitions`                       | 30      | 15%        | Users mostly read competition info; highest read volume in main app         |
| `reads_configs`          | `/v1/configs`                            | 20      | 10%        | Social media links, site settings; read on every landing page load          |
| `reads_community_groups` | `/v1/community-groups`                   | 10      | 5%         | Community group listing on landing page                                     |
| `reads_testimonials`     | `/v1/testimonials`                       | 10      | 5%         | Testimonial showcase on landing page                                        |

### B. Financial Bottlenecks (Creates) - 15% of test traffic (3 scenarios, 30 VUs max)

Core transaction endpoints where failure equals broken user workflows.

| Scenario                    | Endpoint                | Max VUs | Percentage | Notes                                                                     |
| --------------------------- | ----------------------- | ------- | ---------- | ------------------------------------------------------------------------- |
| `creates_fund_applications` | `/v1/fund-applications` | 12      | 6%         | Fund application submission; involves status workflow, potential DB locks |
| `creates_achievements`      | `/v1/achievements`      | 10      | 5%         | Achievement submission with approval status                               |
| `creates_teams`             | `/v1/teams`             | 8       | 4%         | Team creation; prerequisite for fund app & achievement submission         |

### C. Concurrency Traps (Updates) - 5% of test traffic (2 scenarios, 10 VUs max)

Endpoints most likely to suffer from race conditions under concurrent access.

| Scenario                    | Endpoint                     | Max VUs | Percentage | Notes                                                                                   |
| --------------------------- | ---------------------------- | ------- | ---------- | --------------------------------------------------------------------------------------- |
| `updates_fund_applications` | `/v1/fund-applications/{id}` | 6       | 3%         | Status transitions (Pending/Accepted/Rejected); lock contention under concurrent review |
| `updates_achievements`      | `/v1/achievements/{id}`      | 4       | 2%         | Status transitions (Pending/Accepted/Rejected); same pattern as fund applications       |

### D. Noise (Excluded from test)

All other endpoints are excluded: reference data CRUD (degrees, faculties, majors, competition types, etc.), admin-only operations (configs write, personas write, groups/permissions management), delete operations, token management, and nested member detail endpoints.

---

## Pre-Test Checklist

- [ ] All endpoints classified in the matrix above
- [ ] Monitoring dashboards configured and accessible
- [ ] Database slow query log enabled
- [ ] APM tracing active on all application nodes
- [ ] Auto-scaler policies verified and documented
- [ ] Baseline metrics captured (normal traffic P50/P95/P99)
- [ ] Rollback plan documented in case of production impact
- [ ] Test environment mirrors production topology
- [ ] Load testing tool configured with multi-scenario architecture (11 scenarios using ramping-vus executor)
- [ ] Traffic distribution verified: 80% reads, 15% creates, 5% updates
- [ ] Smooth VU ramp-up configured: 20 minutes total (4 stages of 5 min each)
- [ ] Thresholds configured with abortOnFail for automatic test stop
- [ ] Team on standby for real-time monitoring during test
