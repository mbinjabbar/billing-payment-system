import { ApiResponse } from "../utils/ApiResponse.js";

export const responseMiddleware = (req, res, next) => {
    res.api = new ApiResponse(res);
    next();
}