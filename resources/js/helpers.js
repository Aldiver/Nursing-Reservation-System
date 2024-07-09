export const formatTime = (time) => {
    const [hour, minute, second] = time.split(":");
    let formattedHour = parseInt(hour, 10);
    const ampm = formattedHour >= 12 ? "PM" : "AM";

    if (formattedHour > 12) {
        formattedHour -= 12;
    } else if (formattedHour === 0) {
        formattedHour = 12;
    }
    return `${formattedHour}:${minute} ${ampm}`;
};

export const formatDate = (inputDate) => {
    const date = new Date(inputDate);
    const options = { year: "numeric", month: "long", day: "numeric" };
    return date.toLocaleDateString("en-US", options);
};
