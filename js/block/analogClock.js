import CoolClock from '../lib/coolclock';
import $ from "jquery";


class View {
    constructor (serverTime) {
        this.serverTime = serverTime * 1000 || Date.now()
        this.clearClock();
        this.addClock('clock_big', 200);
        this.addClock('clock_medium', 160);
        this.addClock('clock', 120);
        this.initClock();
    }

    clearClock()
    {
        console.log('cleared old clock')
        $("#Uhr").children(".container").empty();
    }

    addClock(id, diameter) {
        console.log(`added new clock instances ${id}`)
        let date = new Date(this.serverTime);
        let clock = document.createElement("canvas");
        clock.setAttribute(`id`, id + this.serverTime);
        clock.classList.add(`CoolClock:themed:${diameter}:showSecondHand::${this.serverTime}`);
        clock.classList.add(`myClock`);
        clock.setAttribute(`width`, diameter * 2);
        clock.setAttribute(`height`, diameter * 2)
        clock.setAttribute(`style`, `width: ${diameter * 2}px; height: ${diameter * 2}px`);
        $(clock).appendTo($(`#${id}`));

        let dayname = date.toLocaleString("de-de", {weekday: 'long'});
        let day = String(date.getDate()).padStart(2, '0');
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let year = date.getFullYear();
        let dateString = dayname + ', ' + day + '.' + month + '.' + year + '<br />';
        $("#aufrufanzeige_Datum").html(dateString);
    }

    initClock() {
        //console.log('start clock')
        CoolClock.config.skins = {
            themed: {
                outerBorder: { lineWidth: 1, radius:95, color: "black", alpha: 0 },
                smallIndicator: { lineWidth: 1, startAt: 89, endAt: 93, color: "#4C4C4C", alpha: 1 },
                largeIndicator: { lineWidth: 5, startAt: 85, endAt: 93, color: "#4C4C4C", alpha: 1 },
                hourHand: { lineWidth: 7, startAt: -10, endAt: 50, color: "#4C4C4C", alpha: 1 },
                minuteHand: { lineWidth: 5, startAt: -10, endAt: 75, color: "#4C4C4C", alpha: 1 },
                secondHand: { lineWidth: 1, startAt: -10, endAt: 85, color: "#4C4C4C", alpha: 1 },
                secondDecoration: { lineWidth: 1, startAt: 70, radius: 4, fillColor: "red", color: "red", alpha: 0 }
            }
        };
        CoolClock.findAndCreateClocks();
    }
}

export default View;
