export class ApiResponse {
    constructor(res) {
        this.res = res;
    }

    success(data = null, message = 'success', statusCode = 200) {
        return this.res.status(statusCode).json({
            success: true,
            message,
            data,
        });
    }

    created(data = {}, message = 'Created successfully') {
        return this.success(data, message, 201);
    }

    error(message = 'Something went wrong', statusCode = 500) {
        return this.res.status(statusCode).json({
            success: false,
            message,
        });
    }
}