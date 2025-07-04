<template>
    <div class="home">
        <h1>{{ welcomeMessage }}</h1>
        <p>{{ description }}</p>
        <button @click="updateMessage" class="btn">Click Me</button>
        <p v-if="clicked">Button clicked {{ clickcount }} times</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAppStore } from '../store/app'

const store = useAppStore()

const welcomeMessage = ref('Welcome to the Hotel Booking System');
const description = ref('This is a simple hotel booking system built with Vue.js and Laravel.');
const clicked = ref(false);
const clickcount = ref(0);

//methods
const updateMessage=()=>{
    clicked.value=true;
    clickcount.value++;
    store.increment()
    console.log('store count:',store.count);
     
}
</script>

<style scoped>
.home {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  color: #42b983;
  margin-bottom: 20px;
}

p {
  font-size: 18px;
  margin-bottom: 15px;
}

.btn {
  background-color: #42b983;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
}

.btn:hover {
  background-color: #369870;
}
</style>