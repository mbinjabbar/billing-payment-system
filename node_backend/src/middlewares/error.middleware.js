export const notFound = (req, res) => {
    return res.status(404).json({ success: false, message: "Route not found" });
}

export const errorHandler = (err, req, res, next) => {
    res.api.error(err.message || "Internal server error",err.status || 500, err.errors || null);
};