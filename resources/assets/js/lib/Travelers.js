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

    add() {
        let cleanData = this.input.replace(/[\s|,]/g, '');

        if (cleanData != '') {
            return super.add();
        }
    }
}

export default Hashtags;
