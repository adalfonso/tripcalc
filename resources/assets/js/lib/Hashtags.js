import Queue from '../lib/Queue.js';

class Hashtags extends Queue {

    constructor(data) {

        super();

        if (typeof data === 'string') {
           this.parseHashtags(data);
        }
    }

    add(data) {
        let cleanData = data.replace(/[\s|,]/g, '');

        if (cleanData != '') {
            return super.add(cleanData);
        }
    }

    parseHashtags(data) {
        this.items = data.split(',');
    }

}

export default Hashtags;