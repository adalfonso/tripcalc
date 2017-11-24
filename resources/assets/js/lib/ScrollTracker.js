class ScrollTracker {

    constructor(elem, more) {
        this.elem = elem;
        this.more = more;

        this.threshold = .95;
        this.timeout = 300;

        this.elem.addEventListener('scroll', this.scroll.bind(this));
    }

    scroll() {
        clearTimeout(this.timer);

        this.timer = setTimeout(() => {
            this.checkPagePosition();
        }, this.timeout);
    }

    checkPagePosition() {
        let elem = this.elem === window
            ? document.querySelector('html')
            : this.elem;

        let scrollAmount = elem.scrollTop;
        let maximumScroll = elem.scrollHeight - elem.clientHeight;

        let fires = scrollAmount / maximumScroll >= this.threshold;

        if (fires) {
            this.more();
        }
    }
}

export default ScrollTracker;
