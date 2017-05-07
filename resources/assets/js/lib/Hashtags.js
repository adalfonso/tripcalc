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

        let hashtags = this.input.replace(/#/g, ' ').trim().split(/\s+/);

        hashtags.forEach( hashtag => {
            super.add(hashtag);
        });
    }
}

export default Hashtags;
