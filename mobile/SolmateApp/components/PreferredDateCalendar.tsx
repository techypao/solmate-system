import React, {useEffect, useMemo, useState} from 'react';
import {Pressable, StyleSheet, Text, View} from 'react-native';
import {Calendar} from 'react-native-calendars';

type MarkedDateConfig = {
  disabled?: boolean;
  disableTouchEvent?: boolean;
  selected?: boolean;
  selectedColor?: string;
  selectedTextColor?: string;
  marked?: boolean;
  dotColor?: string;
};

type PreferredDateCalendarProps = {
  label: string;
  selectedDate: string;
  unavailableDates: string[];
  availabilityMessage?: string;
  helperText: string;
  errorText?: string;
  reservedDateMessage: string;
  onSelectDate: (date: string) => void;
  onClearDate: () => void;
};

function formatDateForApi(date: Date) {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function formatDateForDisplay(value: string) {
  return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function getMonthStart(value: string) {
  return `${value.slice(0, 7)}-01`;
}

export default function PreferredDateCalendar({
  label,
  selectedDate,
  unavailableDates,
  availabilityMessage,
  helperText,
  errorText,
  reservedDateMessage,
  onSelectDate,
  onClearDate,
}: PreferredDateCalendarProps) {
  const todayKey = useMemo(() => formatDateForApi(new Date()), []);
  const [visibleMonth, setVisibleMonth] = useState(
    getMonthStart(selectedDate || todayKey),
  );
  const reservedDatePreview = useMemo(
    () => unavailableDates.filter(date => date >= todayKey).slice(0, 5),
    [todayKey, unavailableDates],
  );
  const isSelectedDateUnavailable = Boolean(
    selectedDate && unavailableDates.includes(selectedDate),
  );
  const markedDates = useMemo<Record<string, MarkedDateConfig>>(() => {
    const nextMarkedDates: Record<string, MarkedDateConfig> = {};

    unavailableDates.forEach(date => {
      nextMarkedDates[date] = {
        disabled: true,
        disableTouchEvent: true,
        marked: true,
        dotColor: '#DDE7EE',
      };
    });

    if (selectedDate) {
      const selectedDateIsUnavailable = unavailableDates.includes(selectedDate);

      nextMarkedDates[selectedDate] = {
        ...nextMarkedDates[selectedDate],
        selected: true,
        selectedColor: selectedDateIsUnavailable ? '#fecaca' : '#123A5A',
        selectedTextColor: selectedDateIsUnavailable ? '#991b1b' : '#ffffff',
      };
    }

    return nextMarkedDates;
  }, [selectedDate, unavailableDates]);

  useEffect(() => {
    if (!selectedDate) {
      return;
    }

    setVisibleMonth(getMonthStart(selectedDate));
  }, [selectedDate]);

  return (
    <View style={styles.fieldGroup}>
      <View style={styles.fieldHeader}>
        <Text style={styles.fieldLabel}>{label}</Text>
      </View>

      <View
        style={[
          styles.calendarCard,
          errorText || isSelectedDateUnavailable ? styles.calendarCardError : null,
        ]}>
        <View style={styles.selectedDateRow}>
          <View>
            <Text style={styles.selectedDateLabel}>Selected date</Text>
            <Text
              style={[
                styles.selectedDateValue,
                !selectedDate ? styles.placeholderText : null,
              ]}>
              {selectedDate ? formatDateForDisplay(selectedDate) : 'No date selected'}
            </Text>
          </View>

          {selectedDate ? (
            <Pressable onPress={onClearDate} style={styles.clearDateButton}>
              <Text style={styles.clearDateText}>Clear</Text>
            </Pressable>
          ) : null}
        </View>

        <View style={styles.legendRow}>
          <View style={styles.legendItem}>
            <View style={[styles.legendSwatch, styles.legendSelected]} />
            <Text style={styles.legendText}>Selected</Text>
          </View>
          <View style={styles.legendItem}>
            <View style={[styles.legendSwatch, styles.legendUnavailable]} />
            <Text style={styles.legendText}>Unavailable</Text>
          </View>
        </View>

        <Calendar
          current={visibleMonth}
          disableAllTouchEventsForDisabledDays={true}
          enableSwipeMonths={true}
          hideExtraDays={false}
          markedDates={markedDates}
          minDate={todayKey}
          onDayPress={day => {
            setVisibleMonth(getMonthStart(day.dateString));
            onSelectDate(day.dateString);
          }}
          onMonthChange={month => {
            setVisibleMonth(getMonthStart(month.dateString));
          }}
          renderArrow={direction => (
            <View style={styles.arrowButton}>
              <Text style={styles.arrowText}>
                {direction === 'left' ? '‹' : '›'}
              </Text>
            </View>
          )}
          theme={{
            arrowColor: '#123A5A',
            calendarBackground: '#ffffff',
            dayTextColor: '#123A5A',
            monthTextColor: '#123A5A',
            textDayFontSize: 15,
            textDayFontWeight: '600',
            textDayHeaderFontSize: 12,
            textDayHeaderFontWeight: '700',
            textDisabledColor: '#c5cdd9',
            textMonthFontSize: 16,
            textMonthFontWeight: '800',
            textSectionTitleColor: '#5E7288',
            todayTextColor: '#F4D000',
          }}
        />
      </View>

      {availabilityMessage ? (
        <Text style={styles.helpText}>{availabilityMessage}</Text>
      ) : null}
      {errorText || isSelectedDateUnavailable ? (
        <Text style={styles.fieldErrorText}>
          {errorText || reservedDateMessage}
        </Text>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  fieldGroup: {
    marginBottom: 18,
  },
  fieldHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  fieldLabel: {
    color: '#123A5A',
    fontSize: 15,
    fontWeight: '800',
  },
  calendarCard: {
    backgroundColor: '#ffffff',
    borderColor: '#DDE7EE',
    borderRadius: 18,
    borderWidth: 1,
    overflow: 'hidden',
    padding: 14,
  },
  calendarCardError: {
    borderColor: '#ef4444',
  },
  selectedDateRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  selectedDateLabel: {
    color: '#5E7288',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  selectedDateValue: {
    color: '#123A5A',
    fontSize: 16,
    fontWeight: '700',
  },
  placeholderText: {
    color: '#5E7288',
  },
  clearDateButton: {
    paddingHorizontal: 4,
    paddingVertical: 4,
  },
  clearDateText: {
    color: '#F4D000',
    fontSize: 13,
    fontWeight: '700',
  },
  legendRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 14,
    marginBottom: 10,
  },
  legendItem: {
    alignItems: 'center',
    flexDirection: 'row',
  },
  legendSwatch: {
    borderRadius: 999,
    height: 10,
    marginRight: 6,
    width: 10,
  },
  legendSelected: {
    backgroundColor: '#123A5A',
  },
  legendUnavailable: {
    backgroundColor: '#DDE7EE',
    borderColor: '#c5cdd9',
    borderWidth: 1,
  },
  legendText: {
    color: '#5E7288',
    fontSize: 12,
    fontWeight: '600',
  },
  arrowButton: {
    alignItems: 'center',
    backgroundColor: '#F8FAFC',
    borderColor: '#c5cdd9',
    borderRadius: 999,
    borderWidth: 1,
    height: 30,
    justifyContent: 'center',
    width: 30,
  },
  arrowText: {
    color: '#123A5A',
    fontSize: 18,
    fontWeight: '800',
    lineHeight: 20,
  },
  helpText: {
    color: '#5E7288',
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
  },
  fieldErrorText: {
    color: '#dc2626',
    fontSize: 13,
    fontWeight: '600',
    marginTop: 8,
  },
});
