var OneSignal = window.OneSignal || [];
OneSignal.push(function() {
    OneSignal.init({
    appId: "", //config APP ID
    autoRegister: false,
    notifyButton: {
        enable: true,
    },
    promptOptions: {
        actionMessage: "We'd like to show you notifications for the latest coupons and updates.",
        acceptButtonText: "ALLOW",
        cancelButtonText: "NO THANKS"
    }
    });
    OneSignal.sendTags({
        path_name: window.location.pathname
    });
    if (Notification.permission === "granted") {
        OneSignal.registerForPushNotifications();
    } else {
        OneSignal.showHttpPrompt();
    }
});