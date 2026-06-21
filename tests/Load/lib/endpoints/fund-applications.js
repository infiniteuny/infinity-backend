import { check } from "k6";
import { trackedRequest, extractId } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";
import {
    generateFundApplicationData,
    generateFundApplicationUpdateData,
} from "../seeders/fund-applications.js";

export function testFundApplications(ids) {
    let res, resourceId;
    const faFields = generateFundApplicationData(ids);

    res = trackedRequest(
        "POST",
        `${BASE_URL}/v1/fund-applications`,
        faFields,
        { ...AUTH_HEADERS, Accept: "application/json" },
        "fund-applications",
    );
    check(res, {
        "FundApplications CREATE": (r) => r.status === 201 || r.status === 200,
    });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest(
            "PUT",
            `${BASE_URL}/v1/fund-applications/${resourceId}`,
            JSON.stringify(generateFundApplicationUpdateData(ids)),
            {
                ...AUTH_HEADERS,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            "fund-applications",
        );
        check(res, { "FundApplications UPDATE": (r) => r.status === 200 });

        res = trackedRequest(
            "DELETE",
            `${BASE_URL}/v1/fund-applications/${resourceId}`,
            null,
            AUTH_HEADERS,
            "fund-applications",
        );
        check(res, { "FundApplications DELETE": (r) => r.status === 200 });
    }
}

export function testFundApplicationsUpdate(ids) {
    let res, resourceId;
    const faFields = generateFundApplicationData(ids);

    // Create resource to get ID
    res = trackedRequest(
        "POST",
        `${BASE_URL}/v1/fund-applications`,
        faFields,
        { ...AUTH_HEADERS, Accept: "application/json" },
        "fund-applications",
    );
    resourceId = extractId(res);

    if (resourceId) {
        // Perform multiple updates to stress the update endpoint
        for (let i = 0; i < 3; i++) {
            res = trackedRequest(
                "PUT",
                `${BASE_URL}/v1/fund-applications/${resourceId}`,
                JSON.stringify(generateFundApplicationUpdateData(ids)),
                {
                    ...AUTH_HEADERS,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                "fund-applications",
            );
            check(res, { "FundApplications UPDATE": (r) => r.status === 200 });
        }

        // Cleanup
        res = trackedRequest(
            "DELETE",
            `${BASE_URL}/v1/fund-applications/${resourceId}`,
            null,
            AUTH_HEADERS,
            "fund-applications",
        );
        check(res, { "FundApplications DELETE": (r) => r.status === 200 });
    }
}
