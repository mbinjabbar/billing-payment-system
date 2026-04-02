export class ApiResponse {
    constructor(res) {
        this.res = res;
    }

    success(data = null, message = "success", statusCode = 200) {
        return this.res.status(statusCode).json({
            success: true,
            message,
            data,
        })
    }

    created(data = {}, message = "created successfully") {
        return this.success(data, message, 201)
    }

    error(message = "Something went wrong", statusCode = 500, errors = null) {
        const body = { success: false, message };
        if (errors) body.errors = errors;
        return this.res.status(statusCode).json(body);
    }

    unauthorized(message = "Unauthorized") {
        return this.error(message, 401);
    }

    conflict(message = "Conflict") {
        return this.error(message, 409);
    }

    notFound(message = "Not found") {
        return this.error(message, 404);
    }

    forbidden(message = "Forbidden") {
        return this.error(message, 403);
    }
}