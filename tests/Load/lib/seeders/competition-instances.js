import http from "k6/http";
import { uid, IMAGE_BIN } from "../helpers.js";

export function generateCompetitionInstanceData(ids) {
    const uniqueId = uid();

    return {
        competition_id: ids.competitionId,
        name: `k6_instance_${uniqueId}`,
        shortname: `KI_${uniqueId}`,
        description: `k6 instance description ${uniqueId}`,
        url: `https://example.com/instance/${uniqueId}`,
        organizer: `k6_organizer_${uniqueId}`,
        organizer_type_id: ids.competitionOrganizerTypeId,
        start_date: "2025-01-01",
        end_date: "2025-12-31",
        location: `k6_location_${uniqueId}`,
        logo: http.file(IMAGE_BIN, `instance_${uniqueId}.jpg`, "image/jpeg"),
    };
}
