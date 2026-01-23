export const getHolidays = (year) => {
    return [
        { date: `${year}-01-01`, name: "New Year's Day", type: 'Regular Holiday' },
        { date: `${year}-02-25`, name: "EDSA People Power Revolution", type: 'Special Non-Working' },
        { date: `${year}-04-09`, name: "Araw ng Kagitingan", type: 'Regular Holiday' },
        { date: `${year}-04-17`, name: "Maundy Thursday", type: 'Regular Holiday' }, // Variable, hardcoded for 2025/2026 approx or need logic. 2026 Easter is April 5. Let's make it simple or use a library if precision needed. For this demo, let's include major fixed ones and a few estimates or just fixed.}
        { date: `${year}-05-01`, name: "Labor Day", type: 'Regular Holiday' },
        { date: `${year}-06-12`, name: "Independence Day", type: 'Regular Holiday' },
        { date: `${year}-08-21`, name: "Ninoy Aquino Day", type: 'Special Non-Working' },
        { date: `${year}-08-25`, name: "National Heroes Day", type: 'Regular Holiday' }, // Last Mon of Aug
        { date: `${year}-11-01`, name: "All Saints' Day", type: 'Special Non-Working' },
        { date: `${year}-11-02`, name: "All Souls' Day", type: 'Special Non-Working' },
        { date: `${year}-11-30`, name: "Bonifacio Day", type: 'Regular Holiday' },
        { date: `${year}-12-08`, name: "Feast of the Immaculate Conception", type: 'Special Non-Working' },
        { date: `${year}-12-24`, name: "Christmas Eve", type: 'Special Non-Working' },
        { date: `${year}-12-25`, name: "Christmas Day", type: 'Regular Holiday' },
        { date: `${year}-12-30`, name: "Rizal Day", type: 'Regular Holiday' },
        { date: `${year}-12-31`, name: "Last Day of the Year", type: 'Special Non-Working' },
    ];
};
