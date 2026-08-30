document.addEventListener('alpine:init', () => {
    Alpine.data('dropdownDatePickerComponent', ({ state, showAge, disabled, readonly }) => ({
        state,
        selectedDay: '',
        selectedMonth: '',
        selectedYear: '',
        showAge,
        disabled,
        readonly,

        init() {
            this.syncFromState();
            this.$watch('state', () => this.syncFromState());
        },

        syncFromState() {
            if (!this.state) {
                this.selectedDay = '';
                this.selectedMonth = '';
                this.selectedYear = '';
                return;
            }

            const stateStr = String(this.state).trim();
            const match = stateStr.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);

            if (match) {
                this.selectedYear = match[1];
                this.selectedMonth = String(parseInt(match[2], 10)).padStart(2, '0');
                this.selectedDay = String(parseInt(match[3], 10)).padStart(2, '0');
            } else {
                const parsed = new Date(stateStr);
                if (!isNaN(parsed.getTime())) {
                    this.selectedYear = String(parsed.getFullYear());
                    this.selectedMonth = String(parsed.getMonth() + 1).padStart(2, '0');
                    this.selectedDay = String(parsed.getDate()).padStart(2, '0');
                }
            }
        },

        get daysInMonth() {
            const y = parseInt(this.selectedYear, 10) || 2000;
            const m = parseInt(this.selectedMonth, 10);
            if (!m) return 31;
            return new Date(y, m, 0).getDate();
        },

        get calculatedAge() {
            if (!this.selectedYear || !this.selectedMonth || !this.selectedDay) {
                return null;
            }
            const birth = new Date(parseInt(this.selectedYear, 10), parseInt(this.selectedMonth, 10) - 1, parseInt(this.selectedDay, 10));
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age >= 0 ? age : null;
        },

        onPartChange() {
            if (this.selectedYear && this.selectedMonth && this.selectedDay) {
                const maxDays = this.daysInMonth;
                if (parseInt(this.selectedDay, 10) > maxDays) {
                    this.selectedDay = String(maxDays).padStart(2, '0');
                }
                this.state = `${this.selectedYear}-${this.selectedMonth}-${this.selectedDay}`;
            } else if (!this.selectedYear && !this.selectedMonth && !this.selectedDay) {
                this.state = null;
            }
        }
    }));
});
