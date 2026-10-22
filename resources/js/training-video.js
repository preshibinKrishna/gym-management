export default () => ({
    exercise: 'Walk & warm up',
    failed: false,
    motionPreference: null,
    motionListener: null,

    init() {
        this.motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.motionListener = (event) => {
            if (event.matches) {
                this.$refs.video.pause();
            }
        };
        this.motionPreference.addEventListener('change', this.motionListener);

        if (!this.motionPreference.matches) {
            this.$refs.video.muted = true;
            this.$refs.video.play().catch(() => {
                // Native controls remain available when the browser blocks autoplay.
            });
        }
    },

    updateExercise() {
        const exercises = ['Walk & warm up', 'Bodyweight squat', 'Reverse lunge', 'Dumbbell curl', 'Shoulder press', 'Push-up'];
        this.exercise = exercises[Math.min(5, Math.floor(this.$refs.video.currentTime / 3))];
    },

    destroy() {
        this.motionPreference?.removeEventListener('change', this.motionListener);
    },
});
