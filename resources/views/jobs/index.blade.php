<x-layout>
    <x-slot:heading>Job Listings</x-slot:heading>

    <div class="space-y-3">
        @foreach($jobs as $job)
            <a href="/jobs/{{$job['id']}}" class="flex flex-col gap-3 px-4 py-6 rounded-xl border border-gray-300">
                <div class="text-blue-300 text-xl font-semibold">{{$job->employer->name}}</div>
                <div>
                    <strong>{{$job['title']}}</strong>: pays {{$job['salary']}} per year
                </div>
            </a>

        @endforeach
        <div>
            {{$jobs->links()}}
        </div>
    </div>


</x-layout>
