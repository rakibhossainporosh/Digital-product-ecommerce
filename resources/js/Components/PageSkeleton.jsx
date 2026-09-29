import React from 'react';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import 'react-loading-skeleton/dist/skeleton.css';

export default function PageSkeleton() {
    return (
        <SkeletonTheme baseColor="#f5f0e6" highlightColor="#fdfbf7" borderRadius="0.75rem">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full animate-pulse">
                {/* Hero / Header Skeleton */}
                <div className="mb-8">
                    <Skeleton height={200} className="w-full" />
                </div>

                {/* Grid / Content Skeleton */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
                    <Skeleton height={100} />
                    <Skeleton height={100} />
                    <Skeleton height={100} />
                    <Skeleton height={100} />
                </div>

                {/* Subheading Skeleton */}
                <div className="mb-6">
                    <Skeleton width={200} height={30} />
                    <Skeleton width={300} height={20} className="mt-2" />
                </div>

                {/* Product/Card Grid Skeleton */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    {[1, 2, 3, 4, 5, 6, 7, 8].map((i) => (
                        <div key={i} className="flex flex-col gap-3">
                            <Skeleton height={180} />
                            <Skeleton width="80%" height={24} />
                            <Skeleton width="40%" height={20} />
                        </div>
                    ))}
                </div>
            </div>
        </SkeletonTheme>
    );
}
