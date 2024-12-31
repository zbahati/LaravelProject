<x-layout>
    <x-slot:heading>
         Job
    </x-slot:heading>

    <h2 class="font-bold text-2xl leading-10">{{$job->title}}</h2>
    <p>
        This job pays  {{$job->salary}} per year.
    </p>

   <div class="mt-3">
       <x-button href="/jobs/{{$job->id}}/edit">Edit Job</x-button>
   </div>
</x-layout>

