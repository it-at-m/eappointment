
class View {
    constructor () {
        setInterval(this.setClock, 1000);
        this.setDate();

    }

    setClock() {
        const now = new Date();

        const secondHand = document.querySelector('.second-hand');
        const minsHand = document.querySelector('.min-hand');
        const hourHand = document.querySelector('.hour-hand');

        const seconds = now.getSeconds();
        const secondsDegrees = ((seconds / 60) * 360) + 90;
        secondHand.style.transform = `rotate(${secondsDegrees}deg)`;
      
        const mins = now.getMinutes();
        const minsDegrees = ((mins / 60) * 360) + ((seconds/60)*6) + 90;
        minsHand.style.transform = `rotate(${minsDegrees}deg)`;
      
        const hour = now.getHours();
        const hourDegrees = ((hour / 12) * 360) + ((mins/60)*30) + 90;
        hourHand.style.transform = `rotate(${hourDegrees}deg)`; 
    }

    setDate() {
        const dateString = document.querySelector('#aufrufanzeige_Datum');
        const now = new Date();
        let options = { weekday: 'long', year: 'numeric', month: '2-digit', day: '2-digit' }
        dateString.innerHTML = new Intl.DateTimeFormat('de-DE', options).format(now)
    }    
}

export default View;
