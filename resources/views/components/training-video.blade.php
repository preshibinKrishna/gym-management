<figure x-data="trainingVideo" class="overflow-hidden rounded-2xl border border-white/10 bg-[#112724] text-white" aria-label="Animated athlete performing a gym training routine">
    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
        <span class="text-xs font-bold uppercase tracking-widest text-mint">The training floor</span>
        <span class="text-xs text-white/65" x-text="exercise">Walk & warm up</span>
    </div>
    <video
        x-ref="video"
        class="aspect-square w-full bg-[#112724] object-contain"
        poster="{{ asset('videos/gym-training-poster.jpg') }}"
        aria-label="18-second 3D animation: walking, squats, lunges, dumbbell curls, shoulder presses, and push-ups"
        muted
        loop
        playsinline
        controls
        preload="metadata"
        x-on:timeupdate="updateExercise()"
        x-on:error="failed = true"
    >
        <source src="{{ asset('videos/gym-training.mp4') }}" type="video/mp4">
        Your browser does not support embedded video. <a href="{{ asset('videos/gym-training.mp4') }}">Watch the training animation.</a>
    </video>
    <figcaption class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-xs text-white/65">
        <span>Walk · Squat · Lunge · Curl · Press · Push-up</span>
        <span class="text-mint">18-second routine</span>
    </figcaption>
    <p x-show="failed" x-cloak class="px-4 pb-4 text-sm">The video could not load. <a href="{{ asset('videos/gym-training.mp4') }}" class="underline">Open the video directly</a>.</p>
</figure>
