import Queue from '../lib/Queue.js';

class Hashtags extends Queue {

    constructor(data) {
        super();

        if (Array.isArray(data)) {
            data.forEach( hashtag => {
                this.add(hashtag);
            });
        }
    }

    add(data = this.input) {
        if (!data) {
            return false;
        }

        let clean = data.replace(/[\s|,]/g, '');

        if (clean !== '') {
            return super.add(clean);
        }
    }
}

export default Hashtags;
