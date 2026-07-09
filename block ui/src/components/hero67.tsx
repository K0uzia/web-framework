import { Calendar } from "lucide-react";

import { Avatar, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface Hero67Props {
  className?: string;
}

const Hero67 = ({ className }: Hero67Props) => {
  return (
    <section className={cn("py-32", className)}>
      <div className="container">
        <div className="z-10 mx-auto flex max-w-4xl flex-col items-center gap-14 text-center">
          <img
            src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/block-1.svg"
            alt="logo"
            className="h-14"
          />
          <div>
            <h1 className="mb-4 text-3xl font-medium text-pretty lg:text-6xl">
              Build Exceptional Online Experiences
            </h1>
            <p className="mx-auto max-w-xl text-muted-foreground">
              Create a website that captures attention, drives engagement, and
              aligns with your goals, all in a matter of days.
            </p>
          </div>
          <div className="flex w-full flex-col items-center justify-center gap-6 lg:flex-row">
            <Button size="lg" className="w-full sm:w-fit">
              <Calendar className="mr-2 h-4" />
              Get Started Today
            </Button>
            <div className="flex flex-col items-center gap-2 lg:items-start">
              <span className="inline-flex items-center -space-x-1">
                <Avatar className="size-7 border">
                  <AvatarImage
                    src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-1.webp"
                    alt="placeholder"
                  />
                </Avatar>
                <Avatar className="size-7 border">
                  <AvatarImage
                    src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-6.webp"
                    alt="placeholder"
                  />
                </Avatar>
                <Avatar className="size-7 border">
                  <AvatarImage
                    src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-3.webp"
                    alt="placeholder"
                  />
                </Avatar>
                <Avatar className="size-7 border">
                  <AvatarImage
                    src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-4.webp"
                    alt="placeholder"
                  />
                </Avatar>
              </span>
              <p className="text-xs text-muted-foreground">
                Trusted by industry leaders
              </p>
            </div>
          </div>
        </div>
        <img
          src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/placeholder-1.svg"
          alt="placeholder"
          className="mx-auto mt-24 aspect-video max-h-[700px] w-full max-w-7xl rounded-t-lg object-cover shadow-md"
        />
      </div>
    </section>
  );
};

export { Hero67 };
