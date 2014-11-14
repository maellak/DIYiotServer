/* 
   Test LED blinking example in C for Arduino Uno. Found in:
   http://balau82.wordpress.com/2011/03/29/programming-arduino-uno-in-pure-c/
*/

#include <avr/io.h>
#include <util/delay.h>
 
enum {
  BLINK_DELAY_MS = 1000,
};

int main (void)
{
  /* Set pin 5 of PORTB for output. */
  DDRB |= _BV(DDB5);
 
  while(1) {
    /* Set pin 5 high to turn LED on. */
    PORTB |= _BV(PORTB5);
    _delay_ms(BLINK_DELAY_MS);
 
    /* Set pin 5 low to turn LED off. */
    PORTB &= ~_BV(PORTB5);
    _delay_ms(BLINK_DELAY_MS);
  }
 
  return 0;
}
