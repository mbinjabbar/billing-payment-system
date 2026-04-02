export const notFound = (req, res) => {
    return res.status(404).json({ success: false, message: "Route not found" });
}

export const errorHandler = (err, req, res, next) => {
    console.log("Global Error:", err.stack);
    res.api.error(
        err.message || "Internal server error",
        err.status || 500,
    );
};