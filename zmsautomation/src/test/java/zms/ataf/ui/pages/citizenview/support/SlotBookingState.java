package zms.ataf.ui.pages.citizenview.support;

/**
 * Mutable booking-slot selection state shared by Ort and Zeit steps.
 */
public final class SlotBookingState {

    /** Last office chosen on the Ort step; used before slot grid screenshots. */
    public int lastSlotBookingOfficeId = -1;

    public String listHourLabel;
    public String listHourBeforeMove;
    public String calendarHourBeforeMove;
    public int hiddenOfficeId = -1;
    public int listAccordionCount;
    public String openListHeading;
    public String markedTimeslotId;
    public String previousTimeslotId;

    /** Epoch seconds of the slot kept after reserve (Meine Termine / ICS). */
    public Long rememberedAppointmentEpoch;
}

