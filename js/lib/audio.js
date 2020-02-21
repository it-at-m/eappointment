export const playSound = url => {
    //console.log(url)
    return new Promise((resolve, reject) => {
        const audioElement = new Audio(url);

        audioElement.addEventListener('timeupdate', () => {
            if (audioElement.currentTime >= audioElement.duration) {
                resolve()
            }
        })

        audioElement.addEventListener('error', err => {
            reject(err)
        })

        audioElement.play()
    })
}
