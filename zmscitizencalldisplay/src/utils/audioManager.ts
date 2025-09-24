import { ref, Ref } from "vue";

export interface QueueItem {
  number: string;
  destination: string;
  status: string;
  appointmentId?: string;
  callTime?: number;
}

class AudioManager {
  private audioElement: HTMLAudioElement | null = null;
  private calledQueueIds: Ref<string[]> = ref([]);
  private isInitialized = false;
  private userHasInteracted = false;

  constructor() {
    this.initializeAudio();
    // Try to trigger Sound permission immediately
    this.triggerSoundPermission();
  }

  private initializeAudio() {
    if (this.isInitialized) return;

    // Use the existing audio element from HTML template - exactly like old system
    this.audioElement = document.getElementById("ring") as HTMLAudioElement;

    if (!this.audioElement) {
      console.warn("Audio Manager - No audio element found");
      return;
    }

    // Audio source is already set in HTML, just configure volume
    this.audioElement.preload = "auto";
    this.audioElement.volume = 0.7;

    // Set up user interaction listeners to enable audio
    this.setupUserInteractionListeners();

    this.isInitialized = true;
  }

  private setupUserInteractionListeners() {
    if (!this.audioElement) return;

    // Listen for any user interaction to enable audio
    const enableAudio = () => {
      this.userHasInteracted = true;
      console.log("Audio Manager - User interaction detected, audio enabled");

      // Remove listeners after first interaction
      document.removeEventListener("click", enableAudio);
      document.removeEventListener("keydown", enableAudio);
      document.removeEventListener("touchstart", enableAudio);
    };

    // Add listeners for various user interactions
    document.addEventListener("click", enableAudio, { once: true });
    document.addEventListener("keydown", enableAudio, { once: true });
    document.addEventListener("touchstart", enableAudio, { once: true });
  }

  /**
   * Get called queue IDs from the current queue data
   * Only items with status "called" or "pickup" are considered called
   */
  getCalledQueueIds(queue: QueueItem[] | null): string[] {
    if (!queue) return [];

    // Only items with status "called" or "pickup" are considered called
    const calledItems = queue.filter(
      (item) => item.status === "called" || item.status === "pickup",
    );
    console.log(
      "Audio Manager - Queue items with called/pickup status:",
      calledItems,
    );

    return calledItems
      .map((item) => item.appointmentId || item.number)
      .filter(Boolean)
      .map((id) => String(id)) // Convert to strings for consistent comparison
      .sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
  }

  /**
   * Check if there are new queue IDs
   */
  hasNewQueueIds(queue: QueueItem[] | null): boolean {
    const currentIds = this.getCalledQueueIds(queue);
    const previousIds = this.calledQueueIds.value;
    const newIds = currentIds.filter((id) => !previousIds.includes(id));
    return newIds.length > 0;
  }

  /**
   * Trigger Sound permission using MediaDevices API with audio output constraints
   * This should trigger the browser's Sound permission dialog
   */
  private async triggerSoundPermission(): Promise<void> {
    try {
      // Try to get audio output devices - this might trigger Sound permission
      if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
        console.log(
          "Audio Manager - Requesting audio output devices to trigger Sound permission...",
        );

        const devices = await navigator.mediaDevices.enumerateDevices();
        const audioOutputs = devices.filter(
          (device) => device.kind === "audiooutput",
        );

        if (audioOutputs.length > 0) {
          console.log(
            "Audio Manager - Audio output devices found, trying to access audio context...",
          );

          // Try to create audio context which might trigger Sound permission
          const audioContext = new (window.AudioContext ||
            (window as any).webkitAudioContext)();

          if (audioContext.state === "suspended") {
            console.log(
              "Audio Manager - Audio context suspended, trying to resume...",
            );
            await audioContext.resume();
          }

          console.log(
            "Audio Manager - Audio context state:",
            audioContext.state,
          );

          if (audioContext.state === "running") {
            console.log(
              "Audio Manager - Sound permission granted via audio context!",
            );
            this.userHasInteracted = true;
            return;
          }
        }
      }

      // Fallback: try to play audio directly
      if (this.audioElement) {
        console.log(
          "Audio Manager - Fallback: attempting direct audio play...",
        );
        await this.audioElement.play();
        console.log(
          "Audio Manager - Sound permission granted via direct audio play!",
        );
        this.userHasInteracted = true;
      }
    } catch (error) {
      console.log(
        "Audio Manager - Sound permission request failed:",
        error instanceof Error ? error.message : String(error),
      );
      console.log(
        "Audio Manager - This is expected - Sound permission requires user interaction",
      );
    }
  }

  /**
   * Play audio for new appointments - exactly like old system
   */
  playAudioForNewAppointments(queue: QueueItem[] | null): void {
    if (!this.audioElement || !this.hasNewQueueIds(queue)) {
      console.log("Audio Manager - Not playing audio:", {
        hasAudioElement: !!this.audioElement,
        hasNewQueueIds: this.hasNewQueueIds(queue),
      });
      return;
    }

    if (!this.userHasInteracted) {
      console.log(
        "Audio Manager - User has not interacted yet, cannot play audio",
      );
      return;
    }

    console.log("Audio Manager - Attempting to play audio");

    // Use exact same approach as old system: $("#ring").get(0).play()
    this.audioElement.currentTime = 0;
    this.audioElement.play().catch((error) => {
      console.warn("Audio Manager - Audio play failed:", error);
    });
    console.log("Audio Manager - Audio play called");
  }

  /**
   * Update the called queue IDs and play audio if needed
   */
  updateAndPlayAudio(queue: QueueItem[] | null): string[] {
    const currentIds = this.getCalledQueueIds(queue);
    const previousIds = this.calledQueueIds.value;

    console.log("Audio Manager - Current queue IDs:", currentIds);
    console.log("Audio Manager - Previous queue IDs:", previousIds);
    console.log("Audio Manager - Full queue data:", queue);

    // Find new IDs: items in current but not in previous
    const newIds = currentIds.filter((id) => !previousIds.includes(id));

    console.log("Audio Manager - New queue IDs:", newIds);

    // Play audio if there are new IDs
    if (newIds.length > 0) {
      console.log(
        "Audio Manager - Playing audio for new appointments:",
        newIds,
      );
      this.playAudioForNewAppointments(queue);
    } else {
      console.log("Audio Manager - No new IDs found, not playing audio");
    }

    // Update the stored called IDs
    this.calledQueueIds.value = currentIds;

    return newIds;
  }

  /**
   * Reset the audio manager state
   */
  reset(): void {
    this.calledQueueIds.value = [];
    if (this.audioElement) {
      this.audioElement.pause();
      this.audioElement.currentTime = 0;
    }
  }

  /**
   * Set volume (0.0 to 1.0)
   */
  setVolume(volume: number): void {
    if (this.audioElement) {
      this.audioElement.volume = Math.max(0, Math.min(1, volume));
    }
  }

  /**
   * Get current volume
   */
  getVolume(): number {
    return this.audioElement?.volume || 0;
  }

  /**
   * Test audio playback (for debugging)
   */
  testAudio(): void {
    console.log("Audio Manager - Testing audio playback");
    console.log("Audio Manager - Audio element:", this.audioElement);
    console.log("Audio Manager - Is initialized:", this.isInitialized);
    console.log("Audio Manager - User has interacted:", this.userHasInteracted);

    if (this.audioElement) {
      console.log(
        "Audio Manager - Audio element ready state:",
        this.audioElement.readyState,
      );
      console.log(
        "Audio Manager - Audio element volume:",
        this.audioElement.volume,
      );
      console.log("Audio Manager - Audio element src:", this.audioElement.src);

      this.audioElement.currentTime = 0;
      this.audioElement
        .play()
        .then(() => {
          console.log("Audio Manager - Test audio played successfully");
        })
        .catch((error) => {
          console.warn("Audio Manager - Test audio failed:", error);
          if (error.name === "NotAllowedError") {
            console.warn(
              "Audio Manager - Audio blocked by browser autoplay policy. User interaction required.",
            );
          }
        });
    } else {
      console.warn("Audio Manager - No audio element available for testing");
    }
  }

  /**
   * Force play audio (for debugging)
   */
  forcePlayAudio(): void {
    console.log("Audio Manager - Force playing audio");
    if (this.audioElement) {
      this.audioElement.currentTime = 0;
      this.audioElement
        .play()
        .then(() => {
          console.log("Audio Manager - Force audio played successfully");
        })
        .catch((error) => {
          console.warn("Audio Manager - Force audio failed:", error);
          if (error.name === "NotAllowedError") {
            console.warn(
              "Audio Manager - Audio blocked by browser autoplay policy. User interaction required.",
            );
          }
        });
    } else {
      console.warn("Audio Manager - No audio element available for force play");
    }
  }

  /**
   * Manually enable audio (for testing)
   * This should trigger the browser to change Sound permission from "Automatic" to "Allow"
   */
  enableAudio(): void {
    this.userHasInteracted = true;
    console.log("Audio Manager - Audio manually enabled");

    // Try to play audio immediately after user interaction to trigger Sound permission
    this.triggerSoundPermissionAfterUserInteraction();
  }

  /**
   * Trigger Sound permission after user interaction
   * This should cause Chrome to change Sound permission from "Automatic" to "Allow"
   */
  private triggerSoundPermissionAfterUserInteraction(): void {
    if (this.audioElement) {
      console.log(
        "Audio Manager - User interacted, attempting to play audio to trigger Sound permission...",
      );

      // Try to play audio immediately after user interaction
      this.audioElement
        .play()
        .then(() => {
          console.log(
            "Audio Manager - Audio played successfully after user interaction!",
          );
          console.log(
            'Audio Manager - Sound permission should now be set to "Allow" in Chrome settings',
          );
        })
        .catch((error) => {
          console.warn(
            "Audio Manager - Audio still blocked after user interaction:",
            error.message,
          );
          console.log(
            "Audio Manager - User may need to manually allow Sound permission in Chrome settings",
          );
        });
    }
  }

  /**
   * Get current state for debugging
   */
  getDebugState(): any {
    return {
      isInitialized: this.isInitialized,
      hasAudioElement: !!this.audioElement,
      calledQueueIds: this.calledQueueIds.value,
      audioVolume: this.audioElement?.volume || 0,
      userHasInteracted: this.userHasInteracted,
    };
  }
}

// Export singleton instance
export const audioManager = new AudioManager();
export default audioManager;
