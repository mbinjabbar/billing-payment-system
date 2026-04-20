export class ApiResponse {
    constructor(res) {
        this.res = res;
    }

    success(data = null, message = 'success', statusCode = 200) {
        if (data && data.data && data.meta) {
            return this.res.status(statusCode).json({
                success: true,
                message,
                data: data.data,
                meta: data.meta
            });
        }

        return this.res.status(statusCode).json({
            success: true,
            message,
            data,
        });
    }

    error(message = 'Something went wrong', statusCode = 500) {
        return this.res.status(statusCode).json({
            success: false,
            message,
        });
    }
}