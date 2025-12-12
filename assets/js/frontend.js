document.addEventListener("DOMContentLoaded", function () {
    require(['core/ajax'], function(Ajax) {
        var video = document.querySelector("video");

        if (!video) {
            return;
        }

        // منع التنفيذ المتكرر
        if (video.dataset.trackingInitialized) {
            return;
        }
        video.dataset.trackingInitialized = "true";

        let courseId = M.cfg.courseId || null;
        let videoId = M.cfg.contextInstanceId || getUrlParameter("id");

        if (!videoId || !courseId) {
            console.error("❌ لم يتم العثور على معرف الفيديو أو معرف الدورة.");
            return;
        }

        let storageKey = `video_watched_${videoId}_${courseId}`;
        let isDataSent = localStorage.getItem(storageKey) === "true";

        if (isDataSent) {
            console.log("🚫 الحدث تم إرساله مسبقًا، لن يتم إرساله مرة أخرى.");
            return;
        }

        let eventSent = false;

        video.addEventListener("timeupdate", function trackProgress() {
            if (!video.duration || eventSent || video.dataset.eventTriggered) return;

            let watched = (video.currentTime / video.duration) * 100;

            if (watched >= 80) {
                console.log(`📌 Video ID: ${videoId}, Course ID: ${courseId}`);
                console.log("🚀 إرسال حدث مشاهدة الفيديو إلى Moodle...");

                eventSent = true;
                video.dataset.eventTriggered = "true";

                let durationISO = formatDuration(video.duration);
                console.log('durationISO: ', durationISO);
                Ajax.call([{
                    methodname: 'local_moodle_lrs_plugin_trigger_video_watched',
                    args: { 
                        videoid: videoId, 
                        courseid: courseId,
                        duration: durationISO,
                    },
                    done: function(response) {
                        console.log("✅ حدث المشاهدة تم إرساله بنجاح!", response);
                        localStorage.setItem(storageKey, "true");
                        video.removeEventListener("timeupdate", trackProgress);
                    },
                    fail: function(error) {
                        console.error("❌ خطأ أثناء إرسال الحدث:", error);
                    }
                }]);
            }
        });

    });

    function getUrlParameter(name) {
        var params = new URLSearchParams(window.location.search);
        return params.get(name);
    }


    function formatDuration(seconds) {
        seconds = Math.max(1, Math.floor(seconds));
    
        let hours = Math.floor(seconds / 3600);
        let minutes = Math.floor((seconds % 3600) / 60);
        let secs = seconds % 60;
    
        return `PT${hours}H${minutes}M${secs}S`;
    }
});
