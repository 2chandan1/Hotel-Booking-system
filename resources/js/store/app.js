import {defineStore} from 'pinia';

export const useAppStore=defineStore('app',{
    state:()=>({
        count:0,
        user:null,
        isLogedIn:false
    }),
    getters:{
        doubleCount:(state)=>state.count *2

    },
    actions:{
        increment(){
            this.count++;

        },
        setUser(user){
            this.user=user;
            this.isLogedIn=true;
        },
        logout(){
            this.user=null;
            this.isLogedIn=false;
        }
    }
})